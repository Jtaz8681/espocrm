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

namespace Espo\Core\FileStorage;

use Psr\Http\Message\StreamInterface;

/**
 * File storing and fetching.
 */
interface Storage
{
    /**
     * Get file contents as a stream.
     */
    public function getStream(Attachment $attachment): StreamInterface;

    /**
     * Store file contents.
     */
    public function putStream(Attachment $attachment, StreamInterface $stream): void;

    /**
     * Whether a file exists.
     */
    public function exists(Attachment $attachment): bool;

    /**
     * Delete a file.
     */
    public function unlink(Attachment $attachment): void;

    /**
     * Get a file size.
     */
    public function getSize(Attachment $attachment): int;
}
