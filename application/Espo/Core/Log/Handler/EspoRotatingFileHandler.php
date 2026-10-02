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

namespace Espo\Core\Log\Handler;

use Espo\Core\Utils\Config;

use Monolog\Level;

class EspoRotatingFileHandler extends EspoFileHandler
{
    protected string $dateFormat = 'Y-m-d';
    protected string $filenameFormat = '{filename}-{date}';
    protected string $filename;
    protected int $maxFiles;

    public function __construct(
        Config $config,
        string $filename,
        int $maxFiles = 0,
        Level $level = Level::Debug,
        bool $bubble = true
    ) {
        $this->filename = $filename;
        $this->maxFiles = $maxFiles;

        parent::__construct($config, $this->getTimedFilename(), $level, $bubble);

        $this->rotate();
    }

    protected function rotate(): void
    {
        if (0 === $this->maxFiles) {
            return; // unlimited number of files for 0
        }

        $filePattern = $this->getFilePattern();
        $dirPath = $this->fileManager->getDirName($this->filename);
        /** @var string[] $logFiles */
        $logFiles = $this->fileManager->getFileList($dirPath, false, $filePattern, true);

        if (!empty($logFiles) && count($logFiles) > $this->maxFiles) {
            usort($logFiles, function ($a, $b) {
                return strcmp($b, $a);
            });

            $logFilesToBeRemoved = array_slice($logFiles, $this->maxFiles);

            $this->fileManager->removeFile($logFilesToBeRemoved, $dirPath);
        }
    }

    protected function getTimedFilename(): string
    {
        $fileInfo = pathinfo($this->filename);

        $timedFilename = str_replace(
            ['{filename}', '{date}'],
            [$fileInfo['filename'], date($this->dateFormat)],
            ($fileInfo['dirname'] ?? '') . '/' . $this->filenameFormat
        );

        if (!empty($fileInfo['extension'])) {
            $timedFilename .= '.' . $fileInfo['extension'];
        }

        return $timedFilename;
    }

    protected function getFilePattern(): string
    {
        $fileInfo = pathinfo($this->filename);

        $glob = str_replace(
            ['{filename}', '{date}'],
            [$fileInfo['filename'], '.*'],
            $this->filenameFormat
        );

        if (!empty($fileInfo['extension'])) {
            $glob .= '\.'.$fileInfo['extension'];
        }

        return '^' . $glob . '$';
    }
}
