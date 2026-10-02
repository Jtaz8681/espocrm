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
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Type\RelationType;

class RelationDefs
{
    /** @var array<string, mixed> */
    private array $params = [];

    private function __construct(private string $name) {}

    public static function create(string $name): self
    {
        return new self($name);
    }

    /**
     * Get a relation name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get a type.
     *
     * @return RelationType::*
     */
    public function getType(): ?string
    {
        /** @var ?RelationType::* */
        return $this->getParam(RelationParam::TYPE);
    }

    /**
     * Clone with a type.
     *
     * @param RelationType::* $type
     */
    public function withType(string $type): self
    {
        return $this->withParam(RelationParam::TYPE, $type);
    }

    /**
     * Clone with a foreign entity type.
     */
    public function withForeignEntityType(string $entityType): self
    {
        return $this->withParam(RelationParam::ENTITY, $entityType);
    }

    /**
     * Get a foreign entity type.
     */
    public function getForeignEntityType(): ?string
    {
        return $this->getParam(RelationParam::ENTITY);
    }

    /**
     * Clone with a foreign relation name.
     */
    public function withForeignRelationName(?string $name): self
    {
        return $this->withParam(RelationParam::FOREIGN, $name);
    }

    /**
     * Get a foreign relation name.
     */
    public function getForeignRelationName(): ?string
    {
        return $this->getParam(RelationParam::FOREIGN);
    }

    /**
     * Clone with a relationship name.
     */
    public function withRelationshipName(string $name): self
    {
        return $this->withParam(RelationParam::RELATION_NAME, $name);
    }

    /**
     * Get a foreign relation name.
     */
    public function getRelationshipName(): ?string
    {
        return $this->getParam(RelationParam::RELATION_NAME);
    }

    /**
     * Clone with a key.
     */
    public function withKey(string $key): self
    {
        return $this->withParam(RelationParam::KEY, $key);
    }

    /**
     * Get a key.
     */
    public function getKey(): ?string
    {
        return $this->getParam(RelationParam::KEY);
    }

    /**
     * Clone with a key.
     */
    public function withForeignKey(string $foreignKey): self
    {
        return $this->withParam(RelationParam::FOREIGN_KEY, $foreignKey);
    }

    /**
     * Get a key.
     */
    public function getForeignKey(): ?string
    {
        return $this->getParam(RelationParam::FOREIGN_KEY);
    }

    /**
     * Clone with middle keys.
     */
    public function withMidKeys(string $midKey, string $foreignMidKey): self
    {
        return $this->withParam(RelationParam::MID_KEYS, [$midKey, $foreignMidKey]);
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
     * Clone with conditions. Conditions are used for relationships that share a same middle table.
     *
     * @param array<string, scalar|(array<int, mixed>)|null> $conditions
     */
    public function withConditions(array $conditions): self
    {
        $obj = clone $this;

        return $obj->withParam(RelationParam::CONDITIONS, $conditions);
    }

    /**
     * Clone with an additional middle table column.
     */
    public function withAdditionalColumn(AttributeDefs $attributeDefs): self
    {
        $obj = clone $this;

        /** @var array<string, array<string, mixed>> $list */
        $list = $obj->getParam(RelationParam::ADDITIONAL_COLUMNS) ?? [];

        $list[$attributeDefs->getName()] = $attributeDefs->toAssoc();

        return $obj->withParam(RelationParam::ADDITIONAL_COLUMNS, $list);
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
