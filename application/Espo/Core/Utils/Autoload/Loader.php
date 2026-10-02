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

namespace Espo\Core\Utils\Autoload;

use Espo\Core\Utils\File\Manager as FileManager;

class Loader
{
    public function __construct(
        private NamespaceLoader $namespaceLoader,
        private FileManager $fileManager
    ) {}

    /**
     *
     * @param array{
     *   psr-4?: array<string, mixed>,
     *   psr-0?: array<string, mixed>,
     *   classmap?: array<string, mixed>,
     *   autoloadFileList?: array<string, mixed>,
     *   files?: array<string, mixed>,
     * } $data
     */
    public function register(array $data): void
    {
        /* load "psr-4", "psr-0", "classmap" */
        $this->namespaceLoader->register($data);

        /* load "autoloadFileList" */
        $this->registerAutoloadFileList($data);

        /* load "files" */
        $this->registerFiles($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function registerAutoloadFileList(array $data): void
    {
        $keyName = 'autoloadFileList';

        if (!isset($data[$keyName])) {
            return;
        }

        foreach ($data[$keyName] as $filePath) {
            if ($this->fileManager->exists($filePath)) {
                require_once($filePath);
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function registerFiles(array $data): void
    {
        $keyName = 'files';

        if (!isset($data[$keyName])) {
            return;
        }

        foreach ($data[$keyName] as $filePath) {
            if ($this->fileManager->exists($filePath)) {
                require_once($filePath);
            }
        }
    }
}
