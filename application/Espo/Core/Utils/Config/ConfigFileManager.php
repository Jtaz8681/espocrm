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
use Espo\Core\Utils\File\Manager as FileManager;

use RuntimeException;

class ConfigFileManager
{
    protected FileManager $fileManager;

    public function __construct()
    {
        $this->fileManager = new FileManager();
    }

    public function setConfig(Config $config): void
    {
        $this->fileManager = new FileManager(
            $config->get('defaultPermissions')
        );
    }

    public function isFile(string $filePath): bool
    {
        return $this->fileManager->isFile($filePath);
    }

    /**
     * @param array<string, mixed> $data
     * @throws RuntimeException
     */
    protected function putPhpContentsInternal(string $path, array $data, bool $useRenaming = false): void
    {
        $result = $this->fileManager->putPhpContents($path, $data, true, $useRenaming);

        if ($result === false) {
            throw new RuntimeException();
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function putPhpContents(string $path, array $data): void
    {
        $this->putPhpContentsInternal($path, $data, true);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function putPhpContentsNoRenaming(string $path, array $data): void
    {
        $this->putPhpContentsInternal($path, $data, false);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPhpContents(string $path): array
    {
        $data = $this->fileManager->getPhpContents($path);

        if (!is_array($data)) {
            throw new RuntimeException("Bad data stored in '{$path}.");
        }

        /** @var array<string, mixed> */
        return $data;
    }
}
