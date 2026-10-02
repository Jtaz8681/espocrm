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

/**
 * Sender parameters.
 *
 * Immutable.
 */
class SenderParams
{
    private ?string $fromAddress = null;
    private ?string $fromName = null;
    private ?string $replyToAddress = null;
    private ?string $replyToName = null;

    /** @var string[] */
    private $paramList = [
        'fromAddress',
        'fromName',
        'replyToAddress',
        'replyToName',
    ];

    public static function create(): self
    {
        return new self();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $params = [];

        foreach ($this->paramList as $name) {
            if ($this->$name !== null) {
                $params[$name] = $this->$name;
            }
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function fromArray(array $params): self
    {
        $obj = new self();

        foreach ($obj->paramList as $name) {
            if (array_key_exists($name, $params)) {
               $obj->$name = $params[$name];
            }
        }

        return $obj;
    }

    public function getFromAddress(): ?string
    {
        return $this->fromAddress;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function getReplyToAddress(): ?string
    {
        return $this->replyToAddress;
    }

    public function getReplyToName(): ?string
    {
        return $this->replyToName;
    }

    public function withFromAddress(?string $fromAddress): self
    {
        $obj = clone $this;
        $obj->fromAddress = $fromAddress;

        return $obj;
    }

    public function withFromName(?string $fromName): self
    {
        $obj = clone $this;
        $obj->fromName = $fromName;

        return $obj;
    }

    public function withReplyToAddress(?string $replyToAddress): self
    {
        $obj = clone $this;
        $obj->replyToAddress = $replyToAddress;

        return $obj;
    }

    public function withReplyToName(?string $replyToName): self
    {
        $obj = clone $this;
        $obj->replyToName = $replyToName;

        return $obj;
    }
}
