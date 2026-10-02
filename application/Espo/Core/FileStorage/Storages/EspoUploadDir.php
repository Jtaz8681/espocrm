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

namespace Espo\Core\FileStorage\Storages;

use Espo\Core\FileStorage\Attachment;
use Espo\Core\FileStorage\Local;
use Espo\Core\FileStorage\Storage;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\File\Exceptions\FileError;

use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\Stream;

class EspoUploadDir implements Storage, Local
{

    public const NAME = 'EspoUploadDir';

    public function __construct(protected FileManager $fileManager)
    {}

    public function unlink(Attachment $attachment): void
    {
        $this->fileManager->unlink(
            $this->getFilePath($attachment)
        );
    }

    public function exists(Attachment $attachment): bool
    {
        $filePath = $this->getFilePath($attachment);

        return $this->fileManager->isFile($filePath);
    }

    public function getSize(Attachment $attachment): int
    {
        $filePath = $this->getFilePath($attachment);

        if (!$this->exists($attachment)) {
            throw new FileError("Could not get size for non-existing file '$filePath'.");
        }

        return $this->fileManager->getSize($filePath);
    }

    public function getStream(Attachment $attachment): StreamInterface
    {
        $filePath = $this->getFilePath($attachment);

        if (!$this->exists($attachment)) {
            throw new FileError("Could not get stream for non-existing '$filePath'.");
        }

        $resource = fopen($filePath, 'r');

        if ($resource === false) {
            throw new FileError("Could not open '$filePath'.");
        }

        return new Stream($resource);
    }

    public function putStream(Attachment $attachment, StreamInterface $stream): void
    {
        $filePath = $this->getFilePath($attachment);

        $stream->rewind();

        // @todo Use a resource to write a file (add a method to the file manager).
        $contents = $stream->getContents();

        $result = $this->fileManager->putContents($filePath, $contents);

        if (!$result) {
            throw new FileError("Could not store a file '$filePath'.");
        }
    }

    public function getLocalFilePath(Attachment $attachment): string
    {
        return $this->getFilePath($attachment);
    }

    /**
     * @return string
     */
    protected function getFilePath(Attachment $attachment)
    {
        $sourceId = $attachment->getSourceId();
        $file = basename($sourceId);

        return 'data/upload/' . $file;
    }
}
