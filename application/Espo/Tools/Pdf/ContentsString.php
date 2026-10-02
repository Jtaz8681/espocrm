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

namespace Espo\Tools\Pdf;

use Psr\Http\Message\StreamInterface;

use GuzzleHttp\Psr7\Stream;

use RuntimeException;

class ContentsString implements Contents
{
    private string $contents;

    private function __construct(string $contents)
    {
        $this->contents = $contents;
    }

    public function getStream(): StreamInterface
    {
        $resource = fopen('php://temp', 'r+');

        if ($resource === false) {
            throw new RuntimeException("Could not open temp.");
        }

        fwrite($resource, $this->getString());
        rewind($resource);

        return new Stream($resource);
    }

    public function getString(): string
    {
        return $this->contents;
    }

    public function getLength(): int
    {
        return strlen($this->contents);
    }

    public static function createFromString(string $contents): ContentsString
    {
        $obj = new self($contents);

        return $obj;
    }
}
