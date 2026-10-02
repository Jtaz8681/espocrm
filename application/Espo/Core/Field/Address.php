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

use Espo\Core\Field\Address\AddressBuilder;

/**
 * An address value object. Immutable.
 */
class Address
{
    public function __construct(
        private ?string $country = null,
        private ?string $state = null,
        private ?string $city = null,
        private ?string $street = null,
        private ?string $postalCode = null
    ) {}

    /**
     * Whether has a street.
     */
    public function hasStreet(): bool
    {
        return $this->street !== null;
    }

    /**
     * Whether has a city.
     */
    public function hasCity(): bool
    {
        return $this->city !== null;
    }

    /**
     * Whether has a country.
     */
    public function hasCountry(): bool
    {
        return $this->country !== null;
    }

    /**
     * Whether has a state.
     */
    public function hasState(): bool
    {
        return $this->state !== null;
    }

    /**
     * Whether has a postal code.
     */
    public function hasPostalCode(): bool
    {
        return $this->postalCode !== null;
    }

    /**
     * Get a street.
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * Get a city.
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * Get a country.
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Get a state.
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * Get a postal code.
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * Clone with a street.
     */
    public function withStreet(?string $street): self
    {
        return self::createBuilder()
            ->clone($this)
            ->setStreet($street)
            ->build();
    }

    /**
     * Clone with a city.
     */
    public function withCity(?string $city): self
    {
        return self::createBuilder()
            ->clone($this)
            ->setCity($city)
            ->build();
    }

    /**
     * Clone with a country.
     */
    public function withCountry(?string $country): self
    {
        return self::createBuilder()
            ->clone($this)
            ->setCountry($country)
            ->build();
    }

    /**
     * Clone with a state.
     */
    public function withState(?string $state): self
    {
        return self::createBuilder()
            ->clone($this)
            ->setState($state)
            ->build();
    }

    /**
     * Clone with a postal code.
     */
    public function withPostalCode(?string $postalCode): self
    {
        return self::createBuilder()
            ->clone($this)
            ->setPostalCode($postalCode)
            ->build();
    }

    /**
     * Create an empty address.
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Create a builder.
     */
    public static function createBuilder(): AddressBuilder
    {
        return new AddressBuilder();
    }
}
