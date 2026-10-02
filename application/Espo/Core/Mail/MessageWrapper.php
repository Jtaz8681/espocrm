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

use Espo\Core\Mail\Account\Storage;
use Espo\Core\Mail\Exceptions\ImapError;
use Espo\Core\Mail\Message\Part;

use RuntimeException;

class MessageWrapper implements Message
{
    private ?string $rawHeader = null;
    private ?string $rawContent = null;

    /** @var ?string[] */
    private ?array $flagList = null;

    /**
     * @throws ImapError
     */
    public function __construct(
        private int $id,
        private ?Storage $storage = null,
        private ?Parser $parser = null,
        private ?string $fullRawContent = null,
        private bool $peek = false,
    ) {
        if ($storage) {
            $data = $storage->getHeaderAndFlags($id);

            $this->rawHeader = $data['header'];
            $this->flagList = $data['flags'];
        }

        if (
            !$storage &&
            $this->fullRawContent
        ) {
            $rawHeader = null;
            $rawBody = null;

            if (str_contains($this->fullRawContent, "\r\n\r\n")) {
                [$rawHeader, $rawBody] = explode("\r\n\r\n", $this->fullRawContent, 2);
            } else if (str_contains($this->fullRawContent, "\n\n")) {
                [$rawHeader, $rawBody] = explode("\n\n", $this->fullRawContent, 2);
            }

            $this->rawHeader = $rawHeader;
            $this->rawContent = $rawBody;
        }
    }

    public function getRawHeader(): string
    {
        return $this->rawHeader ?? '';
    }

    public function getParser(): ?Parser
    {
        return $this->parser;
    }

    public function hasHeader(string $name): bool
    {
        if (!$this->parser) {
            throw new RuntimeException();
        }

        return $this->parser->hasHeader($this, $name);
    }

    public function getHeader(string $attribute): ?string
    {
        if (!$this->parser) {
            throw new RuntimeException();
        }

        return $this->parser->getHeader($this, $attribute);
    }

    public function getRawContent(): string
    {
        if (is_null($this->rawContent)) {
            if (!$this->storage) {
                throw new RuntimeException();
            }

            $this->rawContent = $this->storage->getRawContent($this->id, $this->peek);
        }

        return $this->rawContent ?? '';
    }

    public function getFullRawContent(): string
    {
        if ($this->fullRawContent) {
            return $this->fullRawContent;
        }

        return $this->getRawHeader() . "\n" . $this->getRawContent();
    }

    /**
     * @return string[]
     */
    public function getFlags(): array
    {
        return $this->flagList ?? [];
    }

    public function isFetched(): bool
    {
        return (bool) $this->rawHeader;
    }

    /**
     * @return Part[]
     */
    public function getPartList(): array
    {
        if (!$this->parser) {
            throw new RuntimeException();
        }

        return $this->parser->getPartList($this);
    }
}
