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

namespace Espo\Core\Utils\Resource;

use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Resource\FileReader\Params;
use RuntimeException;

/**
 * Reads resource files.
 */
class FileReader
{
    public function __construct(
        private FileManager $fileManager,
        private Metadata $metadata,
        private PathProvider $pathProvider
    ) {}

    /**
     * Read a resource file. Returns NULL if the file does not exist.
     *
     * @throws RuntimeException If the resource does not exist.
     */
    public function read(string $path, Params $params): string
    {
        $exactPath = $this->findExactPath($path, $params);

        if (!$exactPath) {
            throw new RuntimeException("Resource file '$path' does not exist.");
        }

        return $this->fileManager->getContents($exactPath);
    }

    /**
     * Whether a resource file exists.
     */
    public function exists(string $path, Params $params): bool
    {
        return $this->findExactPath($path, $params) !== null;
    }

    private function findExactPath(string $path, Params $params): ?string
    {
        $customPath = $this->pathProvider->getCustom() . $path;

        if ($this->fileManager->isFile($customPath)) {
            return $customPath;
        }

        $moduleName = null;

        if ($params->getScope()) {
            $moduleName = $this->metadata->getScopeModuleName($params->getScope());
        }

        if ($moduleName) {
            $modulePath = $this->buildModulePath($path, $moduleName);

            if ($this->fileManager->isFile($modulePath)) {
                return $modulePath;
            }
        }

        if ($params->getModuleName()) {
            $modulePath = $this->buildModulePath($path, $params->getModuleName());

            if ($this->fileManager->isFile($modulePath)) {
                return $modulePath;
            }
        }

        $corePath = $this->pathProvider->getCore() . $path;

        if ($this->fileManager->isFile($corePath)) {
            return $corePath;
        }

        return null;
    }

    private function buildModulePath(string $path, string $moduleName): string
    {
        return $this->pathProvider->getModule($moduleName) . $path;
    }
}
