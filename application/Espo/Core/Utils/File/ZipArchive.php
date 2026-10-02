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

use RuntimeException;

class ZipArchive
{
    private Manager $fileManager;

    public function __construct(?Manager $fileManager = null)
    {
        if ($fileManager === null) {
            $fileManager = new Manager();
        }

        $this->fileManager = $fileManager;
    }

    /**
     * Unzip archive.
     *
     * @param string $file A path to a zip file.
     * @param string $destination A destination.
     */
    public function unzip(string $file, string $destination): bool
    {
        if (!class_exists('\ZipArchive')) {
            throw new RuntimeException("php-zip extension is not installed. Cannot unzip the file.");
        }

        $zip = new \ZipArchive;

        $res = $zip->open($file);

        if ($res !== true) {
            return false;
        }

        $this->fileManager->mkdir($destination);


        for ($i = 0; $i < $zip->numFiles; $i ++) {
            $filename = $zip->getNameIndex($i);

            if ($filename === false) {
                continue;
            }

            if (
                str_contains($filename, '..') ||
                str_starts_with($filename, '/') ||
                str_starts_with($filename, '\\')
            ) {
                throw new RuntimeException("No allowed path '$filename'.");
            }

            $zip->extractTo($destination, $filename);
        }

        $zip->close();

        return true;
    }
}
