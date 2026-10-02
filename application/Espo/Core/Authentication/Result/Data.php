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

namespace Espo\Core\Authentication\Result;

use stdClass;

/**
 * Immutable.
 */
class Data
{
    /** @var array<string, mixed> */
    private array $data = [];

    /** @noinspection PhpSameParameterValueInspection */
    private function __construct(
        private ?string $message = null,
        private ?string $failReason = null,
        private ?string $token = null,
        private ?string $view = null
    ) {}

    public static function create(): self
    {
        return new self();
    }

    public static function createWithFailReason(string $failReason): self
    {
        return new self(null, $failReason);
    }

    public static function createWithMessage(string $message): self
    {
        return new self($message);
    }

    public function getView(): ?string
    {
        return $this->view;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getFailReason(): ?string
    {
        return $this->failReason;
    }

    public function getData(): stdClass
    {
        return (object) $this->data;
    }

    public function withMessage(?string $message): self
    {
        $obj = clone $this;
        $obj->message = $message;

        return $obj;
    }

    /** @noinspection PhpUnused */
    public function withFailReason(?string $failReason): self
    {
        $obj = clone $this;
        $obj->failReason = $failReason;

        return $obj;
    }

    /** @noinspection PhpUnused */
    public function withToken(?string $token): self
    {
        $obj = clone $this;
        $obj->token = $token;

        return $obj;
    }

    /** @noinspection PhpUnused */
    public function withView(?string $view): self
    {
        $obj = clone $this;
        $obj->view = $view;

        return $obj;
    }

    /**
     * @param mixed $value
     */
    public function withDataItem(string $name, $value): self
    {
        $obj = clone $this;
        $obj->data[$name] = $value;

        return $obj;
    }
}
