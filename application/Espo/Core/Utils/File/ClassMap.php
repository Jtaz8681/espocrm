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

namespace Espo\Core\Utils\File;

use Espo\Core\Utils\Config;
use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Module;
use Espo\Core\Utils\Module\PathProvider;
use Espo\Core\Utils\Util;

use ReflectionClass;

class ClassMap
{
    public function __construct(
        private FileManager $fileManager,
        private Module $module,
        private DataCache $dataCache,
        private PathProvider $pathProvider,
        private Config\SystemConfig $systemConfig,
    ) {}

    /**
     * Return paths to class files.
     *
     * @param ?string[] $allowedMethods If specified, classes w/o specified method will be ignored.
     * @return array<string, class-string>
     */
    public function getData(
        string $path,
        ?string $cacheKey = null,
        ?array $allowedMethods = null,
        bool $subDirs = false
    ): array {

        $data = null;

        if (
            $cacheKey &&
            $this->dataCache->has($cacheKey) &&
            $this->systemConfig->useCache()
        ) {
            /** @var array<string,class-string> $data */
            $data = $this->dataCache->get($cacheKey);
        }

        if (is_array($data)) {
            return $data;
        }

        $data = $this->getClassNameHash(
            $this->pathProvider->getCore() . $path,
            $allowedMethods,
            $subDirs
        );

        foreach ($this->module->getOrderedList() as $moduleName) {
            $data = array_merge(
                $data,
                $this->getClassNameHash(
                    $this->pathProvider->getModule($moduleName) . $path,
                    $allowedMethods,
                    $subDirs
                )
            );
        }

        $data = array_merge(
            $data,
            $this->getClassNameHash(
                $this->pathProvider->getCustom() . $path,
                $allowedMethods,
                $subDirs
            )
        );

        if ($cacheKey && $this->systemConfig->useCache()) {
            $this->dataCache->store($cacheKey, $data);
        }

        return $data;
    }

    /**
     * @param string[]|string $dirs
     * @param ?string[] $allowedMethods
     * @return array<string,class-string>
     */
    private function getClassNameHash($dirs, ?array $allowedMethods = [], bool $subDirs = false): array
    {
        if (is_string($dirs)) {
            $dirs = (array) $dirs;
        }

        $data = [];

        foreach ($dirs as $dir) {
            if (file_exists($dir)) {
                /** @var string[] $fileList */
                $fileList = $this->fileManager->getFileList($dir, $subDirs, '\.php$', true);

                $this->fillHashFromFileList($fileList, $dir, $allowedMethods, $data);
            }
        }

        return $data;
    }

    /**
     * @param string[] $fileList
     * @param ?string[] $allowedMethods
     * @param array<string,class-string> $data
     */
    private function fillHashFromFileList(
        array $fileList,
        string $dir,
        ?array $allowedMethods,
        array &$data,
        string $category = ''
    ): void {

        foreach ($fileList as $key => $file) {
            if (is_string($key)) {
                if (is_array($file)) {
                    $this->fillHashFromFileList(
                        $file,
                        $dir . '/'. $key,
                        $allowedMethods,
                        $data,
                        $category . $key . '\\'
                    );
                }

                continue;
            }

            $filePath = Util::concatPath($dir, $file);
            $className = Util::getClassName($filePath);

            $fileName = $this->fileManager->getFileName($filePath);

            $class = new ReflectionClass($className);

            if (!$class->isInstantiable()) {
                continue;
            }

            $name = Util::normalizeScopeName(ucfirst($fileName));

            $name = $category . $name;

            if (empty($allowedMethods)) {
                $data[$name] = $className;

                continue;
            }

            foreach ($allowedMethods as $methodName) {
                if (method_exists($className, $methodName)) {
                    $data[$name] = $className;
                }
            }
        }
    }
}
