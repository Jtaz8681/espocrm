<?php
/************************************************************************
 * BugZyro Enterprise System
 *
 * Copyright (C) 2026 Ethos Dive Software. All Rights Reserved.
 *
 * Developed and engineered by Ethos Dive Software.
 * Intellectual property of Ethos Dive Software, with rights of use
 * granted exclusively to BugZyro.
 *
 * PROPRIETARY AND CONFIDENTIAL:
 * This file and the underlying source code are proprietary assets of
 * Ethos Dive Software. Unauthorized copying, distribution, modification,
 * reverse engineering, or public display of this software, via any medium,
 * is strictly prohibited without prior written authorization from
 * Ethos Dive Software.
 ************************************************************************/

namespace Espo\Core\Utils\Config;

use Espo\Core\Utils\Config;

use Exception;
use RuntimeException;

/**
 * Writes into the config.
 */
class ConfigWriter
{
    /** @var array<string, mixed> */
    private $changedData = [];
    /** @var string[] */
    private $removeParamList = [];
    /** @var string[] */
    protected $associativeArrayAttributeList = [
        'currencyRates',
        'database',
        'logger',
        'defaultPermissions',
    ];

    private string $cacheTimestampParam = 'cacheTimestamp';

    public function __construct(
        private Config $config,
        private ConfigWriterFileManager $fileManager,
        private ConfigWriterHelper $helper,
        private InternalConfigHelper $internalConfigHelper
    ) {}

    /**
     * Set a parameter.
     *
     * @param mixed $value
     */
    public function set(string $name, $value): void
    {
        if (in_array($name, $this->associativeArrayAttributeList) && is_object($value)) {
            $value = (array) $value;
        }

        $this->changedData[$name] = $value;
    }

    /**
     * Set multiple parameters.
     *
     * @param array<string, mixed> $params
     */
    public function setMultiple(array $params): void
    {
        foreach ($params as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * Remove a parameter.
     */
    public function remove(string $name): void
    {
        $this->removeParamList[] = $name;
    }

    /**
     * Save config changes to the file.
     */
    public function save(): void
    {
        $changedData = $this->changedData;

        if (!isset($changedData[$this->cacheTimestampParam])) {
            $changedData[$this->cacheTimestampParam] = $this->generateCacheTimestamp();
        }

        $configPath = $this->config->getConfigPath();
        $internalConfigPath = $this->config->getInternalConfigPath();
        $stateConfigPath = $this->config->getStateConfigPath();

        if (!$this->fileManager->isFile($configPath)) {
            throw new RuntimeException("Config file '$configPath' not found.");
        }

        $data = $this->fileManager->getPhpContents($configPath);

        $dataInternal = $this->fileManager->isFile($internalConfigPath) ?
            $this->fileManager->getPhpContents($internalConfigPath) : [];

        $dataState = $this->fileManager->isFile($stateConfigPath) ?
            $this->fileManager->getPhpContents($stateConfigPath) : [];

        if (!is_array($data)) {
            throw new RuntimeException("Could not read config.");
        }

        if (!is_array($dataInternal)) {
            throw new RuntimeException("Could not read config-internal.");
        }

        if (!is_array($dataState)) {
            throw new RuntimeException("Could not read state.");
        }

        $toSaveInternal = false;
        $toSaveState = false;
        $toSaveMain = false;

        foreach (array_merge(array_keys($changedData), $this->removeParamList) as $key) {
            if (array_key_exists($key, $data)) {
                $toSaveMain = true;
            }

            if (array_key_exists($key, $dataInternal)) {
                $toSaveInternal = true;
            }

            if (array_key_exists($key, $dataState)) {
                $toSaveState = true;
            }
        }

        foreach ($changedData as $key => $value) {
            if ($this->internalConfigHelper->isParamForStateConfig($key)) {
                $dataState[$key] = $value;

                unset($data[$key]);
                unset($dataInternal[$key]);

                $toSaveState = true;

                continue;
            }

            if ($this->internalConfigHelper->isParamForInternalConfig($key)) {
                $dataInternal[$key] = $value;

                unset($data[$key]);
                unset($dataState[$key]);

                $toSaveInternal = true;

                continue;
            }

            $data[$key] = $value;

            unset($dataState[$key]);
            unset($dataInternal[$key]);

            $toSaveMain = true;
        }

        foreach ($this->removeParamList as $key) {
            if (array_key_exists($key, $data)) {
                unset($data[$key]);
            }

            if (array_key_exists($key, $dataInternal)) {
                unset($dataInternal[$key]);
            }

            if (array_key_exists($key, $dataState)) {
                unset($dataState[$key]);
            }
        }

        if ($toSaveInternal) {
            $this->saveData($internalConfigPath, $dataInternal, 'microtimeInternal');
        }

        if ($toSaveMain) {
            $this->saveData($configPath, $data, 'microtime');
        }

        if ($toSaveState) {
            $this->saveData($stateConfigPath, $dataState, 'microtimeState');
        }

        $this->changedData = [];
        $this->removeParamList = [];

        $this->config->update();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function saveData(string $path, array &$data, string $timeParam): void
    {
        $data[$timeParam] = $microtime = $this->helper->generateMicrotime();

        try {
            $this->fileManager->putPhpContents($path, $data);
        } catch (Exception) {
            throw new RuntimeException("Could not save config.");
        }

        $reloadedData = $this->fileManager->getPhpContents($path);

        if (
            is_array($reloadedData) &&
            $microtime === ($reloadedData[$timeParam] ?? null)
        ) {
            return;
        }

        try {
            $this->fileManager->putPhpContentsNoRenaming($path, $data);
        } catch (Exception) {
            throw new RuntimeException("Could not save config.");
        }
    }

    /**
     * Update the cache timestamp.
     *
     * @todo Remove? Saving re-writes the cache timestamp anyway.
     */
    public function updateCacheTimestamp(): void
    {
        $this->set($this->cacheTimestampParam, $this->generateCacheTimestamp());
    }

    protected function generateCacheTimestamp(): int
    {
        return $this->helper->generateCacheTimestamp();
    }
}
