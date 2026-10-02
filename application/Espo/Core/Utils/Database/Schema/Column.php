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

namespace Espo\Core\Utils\Database\Schema;

/**
 * A DB column parameters.
 */
class Column
{
    private bool $notNull = false;
    private ?int $length = null;
    private mixed $default = null;
    private ?bool $autoincrement = null;
    private ?int $precision = null;
    private ?int $scale = null;
    private ?bool $unsigned = null;
    private ?bool $fixed = null;
    private ?string $collation = null;
    private ?string $charset = null;

    private function __construct(
        private string $name,
        private string $type
    ) {}

    public static function create(string $name, string $type): self
    {
        return new self($name, $type);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isNotNull(): bool
    {
        return $this->notNull;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getAutoincrement(): ?bool
    {
        return $this->autoincrement;
    }

    public function getUnsigned(): ?bool
    {
        return $this->unsigned;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function getFixed(): ?bool
    {
        return $this->fixed;
    }

    public function getCollation(): ?string
    {
        return $this->collation;
    }

    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function withNotNull(bool $notNull = true): self
    {
        $obj = clone $this;
        $obj->notNull = $notNull;

        return $obj;
    }

    public function withLength(?int $length): self
    {
        $obj = clone $this;
        $obj->length = $length;

        return $obj;
    }

    public function withDefault(mixed $default): self
    {
        $obj = clone $this;
        $obj->default = $default;

        return $obj;
    }

    public function withAutoincrement(?bool $autoincrement = true): self
    {
        $obj = clone $this;
        $obj->autoincrement = $autoincrement;

        return $obj;
    }

    /**
     * Unsigned. Supported only by MySQL.
     */
    public function withUnsigned(?bool $unsigned = true): self
    {
        $obj = clone $this;
        $obj->unsigned = $unsigned;

        return $obj;
    }

    public function withPrecision(?int $precision): self
    {
        $obj = clone $this;
        $obj->precision = $precision;

        return $obj;
    }

    public function withScale(?int $scale): self
    {
        $obj = clone $this;
        $obj->scale = $scale;

        return $obj;
    }

    /**
     * Fixed length. For string and binary types.
     */
    public function withFixed(?bool $fixed = true): self
    {
        $obj = clone $this;
        $obj->fixed = $fixed;

        return $obj;
    }

    public function withCollation(?string $collation): self
    {
        $obj = clone $this;
        $obj->collation = $collation;

        return $obj;
    }

    public function withCharset(?string $charset): self
    {
        $obj = clone $this;
        $obj->charset = $charset;

        return $obj;
    }
}
