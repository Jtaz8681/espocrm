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

namespace Espo\Core\Field;

use InvalidArgumentException;

/**
 * A phone number value. Immutable.
 */
class PhoneNumber
{
    private string $number;
    private ?string $type = null;
    private bool $isOptedOut = false;
    private bool $isInvalid = false;

    public function __construct(string $number)
    {
        if ($number === '') {
            throw new InvalidArgumentException("Empty phone number.");
        }

        $this->number = $number;
    }

    /**
     * Get a type.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Get a number.
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * Whether opted-out.
     */
    public function isOptedOut(): bool
    {
        return $this->isOptedOut;
    }

    /**
     * Whether invalid.
     */
    public function isInvalid(): bool
    {
        return $this->isInvalid;
    }

    /**
     * Clone with a type.
     */
    public function withType(string $type): self
    {
        $obj = $this->clone();

        $obj->type = $type;

        return $obj;
    }

    /**
     * Clone set invalid.
     */
    public function invalid(): self
    {
        $obj = $this->clone();

        $obj->isInvalid = true;

        return $obj;
    }

    /**
     * Clone set not invalid.
     */
    public function notInvalid(): self
    {
        $obj = $this->clone();

        $obj->isInvalid = false;

        return $obj;
    }

    /**
     * Clone set opted-out.
     */
    public function optedOut(): self
    {
        $obj = $this->clone();

        $obj->isOptedOut = true;

        return $obj;
    }

    /**
     * Clone set not opted-out.
     */
    public function notOptedOut(): self
    {
        $obj = $this->clone();

        $obj->isOptedOut = false;

        return $obj;
    }

    /**
     * Create with a number.
     *
     * @throws InvalidArgumentException
     */
    public static function create(string $number): self
    {
        return new self($number);
    }

    /**
     * Create from a number and type.
     *
     * @throws InvalidArgumentException
     */
    public static function createWithType(string $number, string $type): self
    {
        return self::create($number)->withType($type);
    }

    private function clone(): self
    {
        $obj = new self($this->number);

        $obj->type = $this->type;
        $obj->isInvalid = $this->isInvalid;
        $obj->isOptedOut = $this->isOptedOut;

        return $obj;
    }
}
