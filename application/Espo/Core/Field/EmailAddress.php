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

use const FILTER_VALIDATE_EMAIL;

/**
 * An email address value. Immutable.
 */
class EmailAddress
{
    private string $address;
    private bool $isOptedOut = false;
    private bool $isInvalid = false;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $address)
    {
        if ($address === '') {
            throw new InvalidArgumentException("Empty email address.");
        }

        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Not valid email address '$address'.");
        }

        $this->address = $address;
    }

    /**
     * Get an address.
     */
    public function getAddress(): string
    {
        return $this->address;
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
     * Create from an address.
     *
     * @throws InvalidArgumentException
     */
    public static function create(string $address): self
    {
        return new self($address);
    }

    private function clone(): self
    {
        $obj = new self($this->address);

        $obj->isInvalid = $this->isInvalid;
        $obj->isOptedOut = $this->isOptedOut;

        return $obj;
    }
}
