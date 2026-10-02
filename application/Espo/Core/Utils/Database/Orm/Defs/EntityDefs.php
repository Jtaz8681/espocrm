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

use Espo\ORM\Defs\Params\EntityParam;

/**
 * Immutable.
 */
class EntityDefs
{
    /** @var array<string, AttributeDefs> */
    private array $attributes = [];
    /** @var array<string, RelationDefs> */
    private array $relations = [];
    /** @var array<string, IndexDefs> */
    private array $indexes = [];

    private function __construct() {}

    public static function create(): self
    {
        return new self();
    }

    public function withAttribute(AttributeDefs $attributeDefs): self
    {
        $obj = clone $this;
        $obj->attributes[$attributeDefs->getName()] = $attributeDefs;

        return $obj;
    }

    public function withRelation(RelationDefs $relationDefs): self
    {
        $obj = clone $this;
        $obj->relations[$relationDefs->getName()] = $relationDefs;

        return $obj;
    }

    public function withIndex(IndexDefs $index): self
    {
        $obj = clone $this;
        $obj->indexes[$index->getName()] = $index;

        return $obj;
    }

    public function withoutAttribute(string $name): self
    {
        $obj = clone $this;
        unset($obj->attributes[$name]);

        return $obj;
    }

    public function withoutRelation(string $name): self
    {
        $obj = clone $this;
        unset($obj->relations[$name]);

        return $obj;
    }

    public function withoutIndex(string $name): self
    {
        $obj = clone $this;
        unset($obj->indexes[$name]);

        return $obj;
    }

    public function getAttribute(string $name): ?AttributeDefs
    {
        return $this->attributes[$name] ?? null;
    }

    public function getRelation(string $name): ?RelationDefs
    {
        return $this->relations[$name] ?? null;
    }

    public function getIndex(string $name): ?IndexDefs
    {
        return $this->indexes[$name] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function toAssoc(): array
    {
        $data = [];

        if (count($this->attributes)) {
            $attributesData = [];

            foreach ($this->attributes as $name => $attributeDefs) {
                $attributesData[$name] = $attributeDefs->toAssoc();
            }

            $data[EntityParam::ATTRIBUTES] = $attributesData;
        }

        if (count($this->relations)) {
            $relationsData = [];

            foreach ($this->relations as $name => $relationDefs) {
                $relationsData[$name] = $relationDefs->toAssoc();
            }

            $data[EntityParam::RELATIONS] = $relationsData;
        }

        if (count($this->indexes)) {
            $indexesData = [];

            foreach ($this->indexes as $name => $indexDefs) {
                $indexesData[$name] = $indexDefs->toAssoc();
            }

            $data[EntityParam::INDEXES] = $indexesData;
        }

        return $data;
    }
}
