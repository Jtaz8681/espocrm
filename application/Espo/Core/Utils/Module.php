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

namespace Espo\Core\Utils;

use Espo\Core\Utils\File\Manager as FileManager;

/**
 * Gets module parameters.
 */
class Module
{
    private const DEFAULT_ORDER = 11;

    /** @var ?array<string, array<string, mixed>> */
    private $data = null;
    /** @var ?string[]  */
    private $list = null;
    /** @var ?string[] */
    private $internalList = null;
    /** @var ?string[]  */
    private $orderedList = null;

    private string $cacheKey = 'modules';
    private string $internalPath = 'application/Espo/Modules';
    private string $customPath = 'custom/Espo/Modules';
    private string $moduleFilePath = 'Resources/module.json';

    public function __construct(
        private FileManager $fileManager,
        private ?DataCache $dataCache = null,
        private bool $useCache = false
    ) {}

    /**
     * Get module parameters.
     *
     * @param string|string[]|null $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get($key = null, $defaultValue = null)
    {
        if ($this->data === null) {
            $this->init();
        }

        assert($this->data !== null);

        if ($key === null) {
            return $this->data;
        }

        return Util::getValueByKey($this->data, $key, $defaultValue);
    }

    private function init(): void
    {
        if (
            $this->useCache &&
            $this->dataCache &&
            $this->dataCache->has($this->cacheKey)
        ) {
            /** @var array<string, array<string, mixed>> $data */
            $data = $this->dataCache->get($this->cacheKey);

            $this->data = $data;

            return;
        }

        $this->data = $this->loadData();

        if ($this->useCache && $this->dataCache) {
            $this->dataCache->store($this->cacheKey, $this->data);
        }
    }

    /**
     * Get an ordered list of modules.
     *
     * @return string[]
     * @todo Use cache if available.
     */
    public function getOrderedList(): array
    {
        if ($this->orderedList !== null) {
            return $this->orderedList;
        }

        $moduleNameList = $this->getList();

        usort($moduleNameList, function (string $m1, string $m2): int {
            $o1 = $this->get([$m1,  'order'], self::DEFAULT_ORDER);
            $o2 = $this->get([$m2,  'order'], self::DEFAULT_ORDER);

            return $o1 - $o2;
        });

        $this->orderedList = $moduleNameList;

        return $this->orderedList;
    }

    /**
     * Get the list of internal modules.
     *
     * @return string[]
     */
    public function getInternalList(): array
    {
        if ($this->internalList === null) {
            $this->internalList = $this->fileManager->getDirList($this->internalPath);
        }

        return $this->internalList;
    }

    private function isInternal(string $moduleName): bool
    {
        return in_array($moduleName, $this->getInternalList());
    }

    public function getModulePath(string $moduleName): string
    {
        $basePath = $this->isInternal($moduleName) ? $this->internalPath : $this->customPath;

        return $basePath . '/' . $moduleName;
    }

    /**
     * Get the list of modules. Not ordered.
     *
     * @return string[]
     */
    public function getList(): array
    {
        if ($this->list === null) {
            $this->list = array_merge(
                $this->getInternalList(),
                $this->fileManager->getDirList($this->customPath)
            );
        }

        return $this->list;
    }

    /**
     * @todo Use event-dispatcher class (passed via constructor).
     * `$this->clearCacheEventDispatcher->subscribe(...);`
     */
    public function clearCache(): void
    {
        $this->data = null;
        $this->list = null;
        $this->internalList = null;
        $this->orderedList = null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function loadData(): array
    {
        $data = [];

        foreach ($this->getList() as $moduleName) {
            $data[$moduleName] = $this->loadModuleData($moduleName);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadModuleData(string $moduleName): array
    {
        $path = $this->getModulePath($moduleName) . '/' . $this->moduleFilePath;

        if (!$this->fileManager->exists($path)) {
            return [
                'order' => self::DEFAULT_ORDER,
            ];
        }

        $contents = $this->fileManager->getContents($path);

        $data = Json::decode($contents, true);

        $data['order'] = $data['order'] ?? self::DEFAULT_ORDER;

        return $data;
    }
}
