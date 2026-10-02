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

use Espo\Core\ORM\DatabaseParamsFactory;
use Espo\Core\Utils\Database\ConfigDataProvider;
use Espo\Core\Utils\Database\Helper as DatabaseHelper;
use Espo\Core\Utils\File\Manager as FileManager;

class SystemRequirements
{
    private const PLATFORM_MYSQL = 'Mysql';
    private const PLATFORM_POSTGRESQL = 'Postgresql';

    /** @var array<string, string> */
    private $pdoExtensionMap = [
        self::PLATFORM_MYSQL => 'pdo_mysql',
        self::PLATFORM_POSTGRESQL => 'pdo_pgsql',
    ];

    public function __construct(
        private Config $config,
        private FileManager $fileManager,
        private System $systemHelper,
        private DatabaseHelper $databaseHelper,
        private DatabaseParamsFactory $databaseParamsFactory,
        private ConfigDataProvider $databaseConfig,
    ) {}

    /**
     * @return array{
     *   php: array<string, array<string, mixed>>,
     *   database: array<string, array<string, mixed>>,
     *   permission: array<string, array<string, mixed>>,
     * }
     */
    public function getAllRequiredList(bool $requiredOnly = false): array
    {
        return [
            'php' => $this->getPhpRequiredList($requiredOnly),
            'database' => $this->getDatabaseRequiredList($requiredOnly),
            'permission' => $this->getRequiredPermissionList(),
        ];
    }

    /**
     * @param ?array<string, mixed> $additionalData
     * @return array{
     *   php?: array<string, array<string, mixed>>,
     *   database?: array<string, array<string, mixed>>,
     *   permission?: array<string, array{type: string, acceptable: int}>,
     * }
     */
    public function getRequiredListByType(
        string $type,
        bool $requiredOnly = false,
        ?array $additionalData = null
    ): array {

        return match ($type) {
            'php' => $this->getPhpRequiredList($requiredOnly),
            'database' => $this->getDatabaseRequiredList($requiredOnly, $additionalData),
            'permission' => $this->getRequiredPermissionList(),
            default => [],
        };
    }

    /**
     * Get required PHP params.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getPhpRequiredList(bool $requiredOnly): array
    {
        $requiredList = [
            'requiredPhpVersion',
            'requiredPhpLibs',
        ];

        if (!$requiredOnly) {
            $requiredList = array_merge($requiredList, [
                'recommendedPhpLibs',
                'recommendedPhpParams',
            ]);
        }

        $list = $this->getRequiredList('phpRequirements', $requiredList);

        $pdoExtension = $this->getPdoExtension();

        if ($pdoExtension) {
            $acceptable = $this->systemHelper->hasPhpExtension($pdoExtension);

            $list[$pdoExtension] = [
                'type' => 'lib',
                'acceptable' => $acceptable,
                'actual' => $acceptable ? 'On' : 'Off',
            ];
        }

        uksort($list, function ($k1, $k2) use ($list) {
            $order = ['version', 'lib', 'param'];

            $a = $list[$k1];
            $b = $list[$k2];

            return array_search($a['type'], $order) - array_search($b['type'], $order);
        });

        return $list;
    }

    private function getPdoExtension(): ?string
    {
        $platform = $this->databaseConfig->getPlatform() ?:
            $this->config->get('database.platform') ?:
                self::PLATFORM_MYSQL;

        return $this->pdoExtensionMap[$platform] ?? null;
    }

    /**
     * Get required DB params.
     *
     * @param ?array<string, mixed> $additionalData
     * @return array<string, array<string, mixed>>
     */
    private function getDatabaseRequiredList(bool $requiredOnly, ?array $additionalData = null): array
    {
        $databaseParams = $this->databaseParamsFactory
            ->createWithMergedAssoc($additionalData['databaseParams'] ?? []);

        $pdo = $this->databaseHelper->createPDO($databaseParams);

        $this->databaseHelper = $this->databaseHelper->withPDO($pdo);

        $databaseTypeName = ucfirst(strtolower($this->databaseHelper->getType()));

        $requiredList = [
            'required' . $databaseTypeName . 'Version',
        ];

        if (!$requiredOnly) {
            $requiredList = array_merge($requiredList, [
                'recommended' . $databaseTypeName . 'Params',
                'connection',
            ]);
        }

        return $this->getRequiredList('databaseRequirements', $requiredList, $additionalData);
    }

    /**
     * Get permission requirements.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getRequiredPermissionList(): array
    {
        return $this->getRequiredList(
            'permissionRequirements',
            ['permissionMap.writable'],
            null,
            [
                'permissionMap.writable' => $this->fileManager->getPermissionUtils()->getWritableList(),
            ]
        );
    }

    /**
     * @param string[] $checkList
     * @param ?array<string, mixed> $additionalData
     * @param array<string, mixed> $predefinedData
     * @return array<string, array<string, mixed>>
     */
    private function getRequiredList(
        string $type,
        array $checkList,
        ?array $additionalData = null,
        array $predefinedData = []
    ): array {

        $list = [];

        foreach ($checkList as $itemName) {
            $type = lcfirst($type);

            $itemValue = $predefinedData[$itemName] ?? $this->config->get($itemName);

            $result = [];

            if ($type === 'phpRequirements') {
                $result = $this->checkPhpRequirements($itemName, $itemValue);
            }

            if ($type === 'databaseRequirements') {
                $result = $this->checkDatabaseRequirements($itemName, $itemValue, $additionalData);
            }

            if ($type === 'permissionRequirements') {
                $result = $this->checkPermissionRequirements($itemName, $itemValue);
            }

            $list = array_merge($list, $result);
        }

        return $list;
    }

    /**
     * Check PHP requirements.
     *
     * @param array<string, mixed>|string $data
     * @return array<string, array<string, mixed>>
     */
    private function checkPhpRequirements(string $type, $data): array
    {
        $list = [];

        switch ($type) {
            case 'requiredPhpVersion':
                $actualVersion = $this->systemHelper->getPhpVersion();
                /** @var string $requiredVersion */
                $requiredVersion = $data;

                $acceptable = true;

                if (version_compare($actualVersion, $requiredVersion) == -1) {
                    $acceptable = false;
                }

                $list[$type] = [
                    'type' => 'version',
                    'acceptable' => $acceptable,
                    'required' => $requiredVersion,
                    'actual' => $actualVersion,
                ];

                break;

            case 'requiredPhpLibs':
            case 'recommendedPhpLibs':
                /** @var string[] $data */
                foreach ($data as $name) {
                    $acceptable = $this->systemHelper->hasPhpExtension($name);

                    $list[$name] = [
                        'type' => 'lib',
                        'acceptable' => $acceptable,
                        'actual' => $acceptable ? 'On' : 'Off',
                    ];
                }

                break;

            case 'recommendedPhpParams':
                /** @var string[] $data */
                foreach ($data as $name => $value) {
                    $requiredValue = $value;
                    $actualValue = $this->systemHelper->getPhpParam($name) ?: '0';

                    $acceptable = Util::convertToByte($actualValue) >= Util::convertToByte($requiredValue);

                    $list[$name] = [
                        'type' => 'param',
                        'acceptable' => $acceptable,
                        'required' => $requiredValue,
                        'actual' => $actualValue,
                    ];
                }

                break;
        }

        return $list;
    }

    /**
     * Check DB requirements.
     *
     * @param array<string, mixed>|string $data
     * @param ?array<string, mixed> $additionalData
     * @return array<string, array<string, mixed>>
     */
    private function checkDatabaseRequirements(string $type, $data, ?array $additionalData = null): array
    {
        $list = [];

        $databaseHelper = $this->databaseHelper;

        $databaseParams = $additionalData['databaseParams'] ?? [];

        switch ($type) {
            case 'requiredMysqlVersion':
            case 'requiredMariadbVersion':
            case 'requiredPostgresqlVersion':
                /** @var string $data */

                $actualVersion = $databaseHelper->getVersion();

                $requiredVersion = $data;

                $acceptable = true;

                if (version_compare($actualVersion, $requiredVersion) == -1) {
                    $acceptable = false;
                }

                $list[$type] = [
                    'type' => 'version',
                    'acceptable' => $acceptable,
                    'required' => $requiredVersion,
                    'actual' => $actualVersion,
                ];
                break;

            case 'recommendedMysqlParams':
            case 'recommendedMariadbParams':
                /** @var string[] $data */
                foreach ($data as $name => $value) {
                    $requiredValue = $value;

                    $actualValue = $databaseHelper->getParam($name);

                    $acceptable = false;

                    switch (gettype($requiredValue)) {
                        case 'integer':
                            if (Util::convertToByte($actualValue ?? '') >= Util::convertToByte($requiredValue)) {
                                $acceptable = true;
                            }

                            break;

                        case 'string':
                            if (strtoupper($actualValue ?? '') === strtoupper($requiredValue)) {
                                $acceptable = true;
                            }

                            break;
                    }

                    $list[$name] = [
                        'type' => 'param',
                        'acceptable' => $acceptable,
                        'required' => $requiredValue,
                        'actual' => $actualValue,
                    ];
                }

                break;

            case 'connection':
                if (!$databaseParams) {
                    $databaseParams = $this->config->get('database');
                }

                $list['host'] = [
                    'type' => 'connection',
                    'acceptable' => true,
                    'actual' => $databaseParams['host'],
                ];

                $list['dbname'] = [
                    'type' => 'connection',
                    'acceptable' => true,
                    'actual' => $databaseParams['dbname'],
                ];

                $list['user'] = [
                    'type' => 'connection',
                    'acceptable' => true,
                    'actual' => $databaseParams['user'],
                ];

                break;
        }

        return $list;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, array<string, mixed>>
     */
    private function checkPermissionRequirements(string $type, $data): array
    {
        $list = [];

        $fileManager = $this->fileManager;

        switch ($type) {
            case 'permissionMap.writable':
                foreach ($data as $item) {
                    $fullPathItem = Util::concatPath($this->systemHelper->getRootDir(), $item);

                    $list[$fullPathItem] = [
                        'type' => 'writable',
                        'acceptable' => $fileManager->isWritable($fullPathItem),
                    ];
                }

                break;

            case 'permissionMap.readable':
                foreach ($data as $item) {
                    $fullPathItem = Util::concatPath($this->systemHelper->getRootDir(), $item);

                    $list[$fullPathItem] = [
                        'type' => 'readable',
                        'acceptable' => $fileManager->isReadable($fullPathItem),
                    ];
                }

                break;
        }

        return $list;
    }
}
