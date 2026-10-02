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

use Espo\Core\Api\Action;
use Espo\Core\Api\Route as RouteItem;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Utils\Config\SystemConfig;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Resource\PathProvider;

/**
 * @phpstan-type RouteArrayShape array{
 *     route: string,
 *     adjustedRoute: string,
 *     method: string,
 *     noAuth?: bool,
 *     params?: array<string, mixed>,
 *     actionClassName: ?class-string<Action>
 *   }
 */
class Route
{
    /** @var ?RouteArrayShape[] */
    private $data = null;
    private string $cacheKey = 'routes';
    private string $routesFileName = 'routes.json';

    public function __construct(
        private Metadata $metadata,
        private FileManager $fileManager,
        private DataCache $dataCache,
        private PathProvider $pathProvider,
        private SystemConfig $systemConfig,
    ) {}

    /**
     * Get all routes.
     *
     * @return RouteItem[]
     */
    public function getFullList(): array
    {
        if (!isset($this->data)) {
            $this->init();
        }

        assert($this->data !== null);

        return array_map(
            function (array $item): RouteItem {
                return new RouteItem(
                    $item['method'],
                    $item['route'],
                    $item['adjustedRoute'],
                    $item['params'] ?? [],
                    $item['noAuth'] ?? false,
                    $item['actionClassName'] ?? null
                );
            },
            $this->data
        );
    }

    private function init(): void
    {
        $useCache = $this->systemConfig->useCache();

        if ($this->dataCache->has($this->cacheKey) && $useCache) {
            /** @var ?(RouteArrayShape[]) $data  */
            $data = $this->dataCache->get($this->cacheKey);

            $this->data = $data;

            return;
        }

        $this->data = $this->unify();

        if ($useCache) {
            $this->dataCache->store($this->cacheKey, $this->data);
        }
    }

    /**
     * @return RouteArrayShape[]
     */
    private function unify(): array
    {
        $customFilePath = $this->pathProvider->getCustom() . $this->routesFileName;
        $coreFilePath = $this->pathProvider->getCore() . $this->routesFileName;

        $data = $this->addDataFromFile([], $customFilePath);

        foreach (array_reverse($this->metadata->getModuleList()) as $module) {
            $moduleFilePath = $this->pathProvider->getModule($module) . $this->routesFileName;

            $data = $this->addDataFromFile($data, $moduleFilePath);
        }

        return $this->addDataFromFile($data, $coreFilePath);
    }

    /**
     * @param RouteArrayShape[] $currentData
     * @return RouteArrayShape[]
     */
    private function addDataFromFile(array $currentData, string $routeFile): array
    {
        if (!$this->fileManager->exists($routeFile)) {
            return $currentData;
        }

        $content = $this->fileManager->getContents($routeFile);

        $data = Json::decode($content, true);

        return $this->appendRoutesToData($currentData, $data);
    }

    /**
     *
     * @param RouteArrayShape[] $data
     * @param RouteArrayShape[] $newData
     * @return RouteArrayShape[]
     */
    private function appendRoutesToData(array $data, array $newData): array
    {
        foreach ($newData as $route) {
            $route['adjustedRoute'] = $this->adjustPath($route['route']);

            if (isset($route['conditions'])) {
                $route['noAuth'] = !($route['conditions']['auth'] ?? true);

                unset($route['conditions']);
            }

            if (self::isRouteInList($route, $data)) {
                continue;
            }

            $data[] = $route;
        }

        return $data;
    }

    /**
     * Check and adjust the route path.
     */
    private function adjustPath(string $path): string
    {
        // to fast route format
        /** @var string $pathFormatted */
        $pathFormatted = preg_replace('/:([a-zA-Z0-9]+)/', '{${1}}', trim($path));

        if (!str_starts_with($pathFormatted, '/')) {
            return '/' . $pathFormatted;
        }

        return $pathFormatted;
    }

    /**
     * @internal
     * @since 9.1.7
     */
    public static function isBadUri(): bool
    {
        /** @var string $serverRequestUri */
        $serverRequestUri = $_SERVER['REQUEST_URI'];

        if (str_starts_with($serverRequestUri, '//')) {
            return true;
        }

        return false;
    }

    public static function detectBasePath(): string
    {
        /** @var string $serverScriptName */
        $serverScriptName = $_SERVER['SCRIPT_NAME'];

        /** @var string $serverRequestUri */
        $serverRequestUri = $_SERVER['REQUEST_URI'];

        /** @var string $scriptName */
        $scriptName = parse_url($serverScriptName , PHP_URL_PATH);

        $scriptNameModified = str_replace('public/api/', 'api/', $scriptName);

        $scriptDir = dirname($scriptNameModified);

        /** @var string $uri */
        /** @noinspection HttpUrlsUsage */
        $uri = parse_url('http://any.com' . $serverRequestUri, PHP_URL_PATH);

        if (stripos($uri, $scriptName) === 0) {
            return $scriptName;
        }

        if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
            return $scriptDir;
        }

        return '';
    }

    /**
     * @param RouteArrayShape $newRoute
     * @param RouteArrayShape[] $routeList
     */
    static private function isRouteInList(array $newRoute, array $routeList): bool
    {
        foreach ($routeList as $route) {
            if (
                $route['route'] === $newRoute['route'] &&
                $route['method'] === $newRoute['method']
            ) {
                return true;
            }
        }

        return false;
    }
}
