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

use Espo\Core\Name\Field;
use Espo\ORM\Entity;
use InvalidArgumentException;

/**
 * A link-multiple item. Immutable.
 */
class LinkMultipleItem
{
    private string $id;
    private ?string $name = null;
    /** @var array<string, mixed> */
    private array $columnData = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $id)
    {
        if ($id === '') {
            throw new InvalidArgumentException("Empty ID.");
        }

        $this->id = $id;
    }

    /**
     * Get an ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get a name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get a column value.
     *
     * @return mixed
     */
    public function getColumnValue(string $column)
    {
        return $this->columnData[$column] ?? null;
    }

    /**
     * Whether a column value is set.
     */
    public function hasColumnValue(string $column): bool
    {
        return array_key_exists($column, $this->columnData);
    }

    /**
     * Get a list of set columns.
     *
     * @return array<int, string>
     */
    public function getColumnList(): array
    {
        return array_keys($this->columnData);
    }

    /**
     * Clone with a name.
     *
     * @param ?string $name Is nullable since 10.0.0.
     */
    public function withName(?string $name): self
    {
        $obj = $this->clone();
        $obj->name = $name;

        return $obj;
    }

    /**
     * Clone with a column value.
     *
     * @param mixed $value
     */
    public function withColumnValue(string $column, $value): self
    {
        $obj = $this->clone();
        $obj->columnData[$column] = $value;

        return $obj;
    }

    /**
     * Create.
     *
     * @throws InvalidArgumentException
     */
    public static function create(string $id, ?string $name = null): self
    {
        $obj = new self($id);
        $obj->name = $name;

        return $obj;
    }

    /**
     * Create from a link.
     *
     * @throws InvalidArgumentException
     *
     * @since 10.0.0
     */
    public static function fromLink(Link $link): self
    {
        return self::create($link->getId(), $link->getName());
    }

    /**
     * Create from an entity.
     *
     * @throws InvalidArgumentException
     *
     * @since 10.0.0
     */
    public static function fromEntity(Entity $entity): self
    {
        return self::create($entity->getId());
    }

    private function clone(): self
    {
        $obj = new self($this->id);

        $obj->name = $this->name;
        $obj->columnData = $this->columnData;

        return $obj;
    }
}
