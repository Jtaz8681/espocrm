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

use Espo\Core\Utils\File\Exceptions\FileError;
use Espo\Core\Utils\File\Manager as FileManager;

use InvalidArgumentException;
use RuntimeException;
use stdClass;

class DataCache
{
    protected string $cacheDir = 'data/cache/application/';

    public function __construct(protected FileManager $fileManager)
    {}

    /**
     * Whether is cached.
     */
    public function has(string $key): bool
    {
        $cacheFile = $this->getCacheFile($key);

        return $this->fileManager->isFile($cacheFile);
    }

    /**
     * Get a stored value.
     *
     * @return array<int|string, mixed>|stdClass
     * @throws FileError
     */
    public function get(string $key)
    {
        $cacheFile = $this->getCacheFile($key);

        return $this->fileManager->getPhpSafeContents($cacheFile);
    }

    /**
     * Try to get a stored value. Returns null if does not exist.
     *
     * @return array<int|string, mixed>|stdClass|null
     * @since 9.3.0
     */
    public function tryGet(string $key)
    {
        if (!$this->has($key)) {
            return null;
        }

        $cacheFile = $this->getCacheFile($key);

        try {
            return $this->fileManager->getPhpSafeContents($cacheFile);
        } catch (FileError) {
            return null;
        }
    }

    /**
     * Store in cache.
     *
     * @param array<int|string, mixed>|stdClass $data
     */
    public function store(string $key, $data): void
    {
        /** @phpstan-var mixed $data */

        if (!$this->checkDataIsValid($data)) {
            throw new InvalidArgumentException("Bad cache data type.");
        }

        $cacheFile = $this->getCacheFile($key);

        $result = $this->fileManager->putPhpContents($cacheFile, $data, true, true);

        if ($result === false) {
            throw new RuntimeException("Could not store '$key'.");
        }
    }

    /**
     * Removes in cache.
     */
    public function clear(string $key): void
    {
        $cacheFile = $this->getCacheFile($key);

        $this->fileManager->removeFile($cacheFile);
    }

    /**
     * @param mixed $data
     * @return bool
     */
    private function checkDataIsValid($data)
    {
        $isInvalid =
            !is_array($data) &&
            !$data instanceof stdClass;

        return !$isInvalid;
    }

    private function getCacheFile(string $key): string
    {
        if (
            $key === '' ||
            preg_match('/[^a-zA-Z0-9_\/\-]/i', $key) ||
            $key[0] === '/' ||
            str_ends_with($key, '/')
        ) {
            throw new InvalidArgumentException("Bad cache key.");
        }

        return $this->cacheDir . $key . '.php';
    }
}
