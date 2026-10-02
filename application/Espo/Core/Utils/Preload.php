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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RecursiveRegexIterator;

/**
 * Scans all PHP files and loads them. Used for a preloading.
 *
 * @see https://www.php.net/manual/en/opcache.preloading.php
 */
class Preload
{
    /**
     * @var string[]
     */
    protected $dirList = [
        'application',
        'vendor/slim',
        'vendor/nikic/fast-route',
    ];

    private int $counter = 0;

    /**
     * @var string[]
     */
    protected $ignoreList = [
        'application/Espo/Core/Mail/Parsers/PhpMimeMailParser/',
        'vendor/nikic/fast-route/test/',
        'vendor/slim/psr7/tests/',
    ];

    public function process(): void
    {
        foreach ($this->dirList as $dir) {
            $this->processForDir($dir);
        }
    }

    public function getCount(): int
    {
        return $this->counter;
    }

    private function processForDir(string $dir): void
    {
        $directory = new RecursiveDirectoryIterator($dir);
        $fullTree = new RecursiveIteratorIterator($directory);
        $phpFiles = new RegexIterator($fullTree, '/.+((?<!Test)+\.php$)/i', RecursiveRegexIterator::GET_MATCH);

        foreach ($phpFiles as $key => $file) {
            $this->processFile($file[0]);
        }
    }

    private function processFile(string $file): void
    {
        if ($this->isFileToBeIgnored($file)) {
            return;
        }

        require_once($file);

        $this->counter++;
    }

    private function isFileToBeIgnored(string $file): bool
    {
        $file = str_replace('\\', '/', $file);

        foreach ($this->ignoreList as $item) {
            if (str_starts_with($file, $item)) {
                return true;
            }
        }

        if (str_contains($file, 'vendor/composer/ClassLoader.php')) {
            return true;
        }

        return false;
    }
}
