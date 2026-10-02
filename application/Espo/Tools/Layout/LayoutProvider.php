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

namespace Espo\Tools\Layout;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Json;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Resource\FileReader;
use Espo\Core\Utils\Resource\FileReader\Params as FileReaderParams;
use RuntimeException;

class LayoutProvider
{
    private string $defaultPath = 'application/Espo/Resources/defaults/layouts';

    /** @internal Used by the portal layout util. */
    protected FileReader $fileReader;

    public function __construct(
        private FileManager $fileManager,
        private InjectableFactory $injectableFactory,
        private Metadata $metadata,
        FileReader $fileReader
    ) {
        $this->fileReader = $fileReader;
    }

    public function get(string $scope, string $name): ?string
    {
        if (
            $this->sanitizeInput($scope) !== $scope ||
            $this->sanitizeInput($name) !== $name
        ) {
            throw new RuntimeException("Bad parameters.");
        }

        $path = "layouts/$scope/$name.json";

        $params = FileReaderParams::create()->withScope($scope);

        $module = $this->getLayoutLocationModule($scope, $name);

        if ($module) {
            $params = $params
                ->withScope(null)
                ->withModuleName($module);
        }

        if ($this->fileReader->exists($path, $params)) {
            return $this->fileReader->read($path, $params);
        }

        $default = $this->getDefault($scope, $name);

        if ($default) {
            return $default;
        }

        foreach (array_reverse($this->metadata->getModuleList()) as $module) {
            $params = FileReaderParams::create()->withModuleName($module);

            if ($this->fileReader->exists($path, $params)) {
                return $this->fileReader->read($path, $params);
            }
        }

        return null;
    }

    private function getLayoutLocationModule(string $scope, string $name): ?string
    {
        return $this->metadata->get("app.layouts.$scope.$name.module");
    }

    private function getDefault(string $scope, string $name): ?string
    {
        $defaultImplClassName = 'Espo\\Custom\\Classes\\DefaultLayouts\\' . ucfirst($name) . 'Type';

        if (!class_exists($defaultImplClassName)) {
            $defaultImplClassName = 'Espo\\Classes\\DefaultLayouts\\' . ucfirst($name) . 'Type';
        }

        if (class_exists($defaultImplClassName)) {
            // @todo Use factory and interface.
            $defaultImpl = $this->injectableFactory->create($defaultImplClassName);

            if (!method_exists($defaultImpl, 'get')) {
                throw new RuntimeException("No 'get' method in '$defaultImplClassName'.");
            }

            $data = $defaultImpl->get($scope);

            return Json::encode($data);
        }

        $filePath = "$this->defaultPath/$name.json";

        if (!$this->fileManager->isFile($filePath)) {
            return null;
        }

        return $this->fileManager->getContents($filePath);
    }

    protected function sanitizeInput(string $name): string
    {
        /** @var string */
        return preg_replace("([.]{2,})", '', $name);
    }
}
