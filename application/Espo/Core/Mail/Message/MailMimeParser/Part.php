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

namespace Espo\Core\Mail\Message\MailMimeParser;

use Espo\Core\Mail\Message\Part as PartInterface;

use ZBateson\MailMimeParser\Message\IMessagePart;

class Part implements PartInterface
{
    private IMessagePart $part;

    public function __construct(IMessagePart $part)
    {
        $this->part = $part;
    }

    public function getContentType(): ?string
    {
        return $this->part->getContentType();
    }

    public function hasContent(): bool
    {
        return $this->part->hasContent();
    }

    public function getContent(): ?string
    {
        return $this->part->getContent();
    }

    public function getContentId(): ?string
    {
        return $this->part->getContentId();
    }

    public function getCharset(): ?string
    {
        return $this->part->getCharset();
    }

    public function getContentDisposition(): ?string
    {
        return $this->part->getContentDisposition();
    }

    public function getFilename(): ?string
    {
        return $this->part->getFilename();
    }
}
