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

namespace Espo\Core\Api;

use Psr\Http\Message\StreamInterface;

/**
 * Representation of an HTTP response. An instance is mutable.
 */
interface Response
{
    /**
     * Get a status code.
     */
    public function getStatusCode(): int;

    /**
     * Get a status reason phrase.
     */
    public function getReasonPhrase(): string;

    /**
     * Set a status code.
     */
    public function setStatus(int $code, ?string $reason = null): self;

    /**
     * Set a specific header.
     */
    public function setHeader(string $name, string $value): self;

    /**
     * Add a specific header.
     */
    public function addHeader(string $name, string $value): self;

    /**
     * Get a header value.
     */
    public function getHeader(string $name): ?string;

    /**
     * Whether a header is set.
     */
    public function hasHeader(string $name): bool;

    /**
     * Get all set header names.
     *
     * @return string[]
     */
    public function getHeaderNames(): array;

    /**
     * Get a header values as an array.
     *
     * @return string[]
     */
    public function getHeaderAsArray(string $name): array;

    /**
     * Write a body.
     */
    public function writeBody(string $string): self;

    /**
     * Set a body.
     */
    public function setBody(StreamInterface $body): self;

    /**
     * Get a body.
     */
    public function getBody(): StreamInterface;
}
