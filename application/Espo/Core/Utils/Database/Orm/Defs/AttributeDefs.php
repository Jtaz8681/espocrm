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

namespace Espo\Core\Utils\Database\Orm\Defs;

use Espo\Core\Utils\Util;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Type\AttributeType;

/**
 * Immutable.
 */
class AttributeDefs
{
    /** @var array<string, mixed> */
    private array $params = [];

    private function __construct(private string $name) {}

    public static function create(string $name): self
    {
        return new self($name);
    }

    /**
     * Get an attribute name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get a type.
     *
     * @return AttributeType::*
     */
    public function getType(): ?string
    {
        /** @var ?AttributeType::* $value */
        $value = $this->getParam(AttributeParam::TYPE);

        return $value;
    }

    /**
     * Clone with a type.
     *
     * @param AttributeType::* $type
     */
    public function withType(string $type): self
    {
        return $this->withParam(AttributeParam::TYPE, $type);
    }

    /**
     * Clone with a DB type.
     */
    public function withDbType(string $dbType): self
    {
        return $this->withParam(AttributeParam::DB_TYPE, $dbType);
    }

    /**
     * Clone with not-storable.
     */
    public function withNotStorable(bool $value = true): self
    {
        return $this->withParam(AttributeParam::NOT_STORABLE, $value);
    }

    /**
     * Clone with a length.
     */
    public function withLength(int $length): self
    {
        return $this->withParam(AttributeParam::LEN, $length);
    }

    /**
     * Clone with a default value.
     */
    public function withDefault(mixed $value): self
    {
        return $this->withParam(AttributeParam::DEFAULT, $value);
    }

    /**
     * Whether a parameter is set.
     */
    public function hasParam(string $name): bool
    {
        return array_key_exists($name, $this->params);
    }

    /**
     * Get a parameter value.
     */
    public function getParam(string $name): mixed
    {
        return $this->params[$name] ?? null;
    }

    /**
     * Clone with a parameter.
     */
    public function withParam(string $name, mixed $value): self
    {
        $obj = clone $this;
        $obj->params[$name] = $value;

        return $obj;
    }

    /**
     * Clone without a parameter.
     */
    public function withoutParam(string $name): self
    {
        $obj = clone $this;
        unset($obj->params[$name]);

        return $obj;
    }

    /**
     * Clone with parameters merged.
     *
     * @param array<string, mixed> $params
     */
    public function withParamsMerged(array $params): self
    {
        $obj = clone $this;

        /** @var array<string, mixed> $params */
        $params = Util::merge($this->params, $params);

        $obj->params = $params;

        return $obj;
    }

    /**
     * To an associative array.
     *
     * @return array<string, mixed>
     */
    public function toAssoc(): array
    {
        return $this->params;
    }
}
