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

namespace Espo\Entities;

use Espo\Core\ORM\Entity;
use Espo\ORM\Name\Attribute;
use stdClass;

class Integration extends Entity
{
    public const ENTITY_TYPE = 'Integration';

    private const ATTR_DATA = 'data';
    private const ATTR_ENABLED = 'enabled';

    public function has(string $attribute): bool
    {
        if ($attribute === Attribute::ID) {
            return (bool) $this->id;
        }

        if ($this->hasAttribute($attribute)) {
            return $this->hasInContainer($attribute);
        }

        return property_exists($this->getData(), $attribute);
    }

    public function get(string $attribute): mixed
    {
        if ($attribute === Attribute::ID) {
            return $this->id;
        }

        if ($this->hasAttribute($attribute)) {
            if ($this->hasInContainer($attribute)) {
                return $this->getFromContainer($attribute);
            }

            return null;
        }

        return $this->getData()->$attribute ?? null;
    }

    public function clear(string $attribute): void
    {
        parent::clear($attribute);

        $data = $this->getData();

        unset($data->$attribute);

        $this->set(self::ATTR_DATA, $data);
    }

    public function setMultiple(array|stdClass $valueMap): static
    {
        return $this->set($valueMap);
    }

    public function set($attribute, $value = null): static
    {
        if (is_object($attribute)) {
            $attribute = get_object_vars($attribute);
        }

        if (is_array($attribute)) {
            $this->populateFromArray($attribute, false);

            return $this;
        }

        $name = $attribute;

        if ($name === Attribute::ID) {
            $this->id = $value;

            return $this;
        }

        if ($this->hasAttribute($name)) {
            $this->setInContainer($name, $value);

            return $this;
        }

        $data = $this->getData();

        $data->$name = $value;

        $this->set(self::ATTR_DATA, $data);

        return $this;
    }

    public function isAttributeChanged(string $name): bool
    {
        if ($name === self::ATTR_DATA) {
            return true;
        }

        return parent::isAttributeChanged($name);
    }

    protected function populateFromArray(array $data, bool $onlyAccessible = true, bool $reset = false): void
    {
        if ($reset) {
            $this->reset();
        }

        foreach ($data as $attribute => $value) {
            if (!is_string($attribute)) {
                continue;
            }

            if ($this->hasAttribute($attribute)) {
                $value = $this->prepareAttributeValue($attribute, $value);
            }

            $this->set($attribute, $value);
        }
    }

    public function getValueMap(): stdClass
    {
        $map = [];

        if (isset($this->id)) {
            $map[Attribute::ID] = $this->id;
        }

        foreach ($this->getAttributeList() as $attribute) {
            if ($attribute === Attribute::ID) {
                continue;
            }

            if ($attribute === self::ATTR_DATA) {
                continue;
            }

            if ($this->has($attribute)) {
                $map[$attribute] = $this->get($attribute);
            }
        }

        $data = $this->getData();

        $map = array_merge($map, get_object_vars($data));

        return (object) $map;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->get(self::ATTR_ENABLED);
    }

    public function getData(): stdClass
    {
        /** @var stdClass */
        return $this->get(self::ATTR_DATA) ?? (object) [];
    }
}
