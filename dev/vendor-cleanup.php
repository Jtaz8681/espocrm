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

$list = [
    'vendor/lasserafn/php-initial-avatar-generator/tests',
    'vendor/lasserafn/php-initial-avatar-generator/src/fonts/script',
    'vendor/lasserafn/php-initial-avatar-generator/src/fonts/FontAwesome5Brands-Regular-400.otf',
    'vendor/lasserafn/php-initial-avatar-generator/src/fonts/FontAwesome5Free-Regular-400.otf',
    'vendor/lasserafn/php-initial-avatar-generator/src/fonts/FontAwesome5Free-Solid-900.otf',
];

foreach ($list as $path) {
    if (is_dir($path)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $fileinfo->isDir() ?
                rmdir($fileinfo->getRealPath()) :
                unlink($fileinfo->getRealPath());
        }

        rmdir($path);

        continue;
    }

    if (!file_exists($path)) {
        continue;
    }

    unlink($path);
}
