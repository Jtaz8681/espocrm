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

class ConfigWriterFileManager
{
    private FileManager $fileManager;

    /**
     * @param ?array{
     *   dir: string|int|null,
     *   file: string|int|null,
     *   user: string|int|null,
     *   group: string|int|null,
     * } $defaultPermissions
     */
    public function __construct(?Config $config = null, ?array $defaultPermissions = null)
    {
        $defaultPermissionsToSet = null;

        if ($defaultPermissions) {
            $defaultPermissionsToSet = $defaultPermissions;
        } else if ($config) {
            $defaultPermissionsToSet = $config->get('defaultPermissions');
        }

        $this->fileManager = new FileManager($defaultPermissionsToSet);
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
     */
    protected function putPhpContentsInternal(string $path, array $data, bool $useRenaming = false): void
    {
        $result = $this->fileManager->putPhpContents($path, $data, true, $useRenaming);

        if ($result === false) {
            throw new RuntimeException();
        }
    }

    /**
     * @param array<string, mixed> $data $data
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
     * Supposed to return array. False means the file is being written or corrupted.
     * @return array<string, mixed>|false
     */
    public function getPhpContents(string $path)
    {
        try {
            $data = $this->fileManager->getPhpContents($path);
        } catch (RuntimeException) {
            return false;
        }

        if (!is_array($data)) {
            return false;
        }

        /** @var array<string, mixed> */
        return $data;
    }
}
