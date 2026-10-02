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

namespace Espo\Core\Mail;

use Espo\Core\Mail\Exceptions\ImapError;
use Espo\Core\Mail\Message\Part;

interface Message
{
    /**
     * Whether has a specific header.
     */
    public function hasHeader(string $name): bool;

    /**
     * Get a specific header.
     */
    public function getHeader(string $attribute): ?string;

    /**
     * Get a raw header part.
     */
    public function getRawHeader(): string;

    /**
     * Get a raw content part.
     *
     * @throws ImapError
     */
    public function getRawContent(): string;

    /**
     * Get a full raw message.
     *
     * @throws ImapError
     */
    public function getFullRawContent(): string;

    /**
     * Get flags.
     *
     * @return string[]
     */
    public function getFlags(): array;

    /**
     * Whether contents is fetched.
     */
    public function isFetched(): bool;

    /**
     * @return Part[]
     */
    public function getPartList(): array;
}
