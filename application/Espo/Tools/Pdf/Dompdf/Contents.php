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

namespace Espo\Tools\Pdf\Dompdf;

use Espo\Tools\Pdf\Contents as ContentsInterface;

use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Dompdf\Dompdf;

use RuntimeException;

class Contents implements ContentsInterface
{
    private ?string $string = null;

    public function __construct(private Dompdf $pdf) {}

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
        if ($this->string === null) {
            $this->string = $this->pdf->output();
        }

        return $this->string ?? '';
    }

    public function getLength(): int
    {
        return strlen($this->getString());
    }
}
