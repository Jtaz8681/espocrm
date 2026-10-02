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

namespace Espo\ORM\Query\Part;

use Espo\ORM\Query\Part\Join\JoinType;
use Espo\ORM\Query\Select;
use LogicException;
use RuntimeException;

/**
 * A join item. Immutable.
 */
class Join
{
    /** A table join. */
    public const MODE_TABLE = 0;
    /** A relation join. */
    public const MODE_RELATION = 1;
    /** A sub-query join. */
    public const MODE_SUB_QUERY = 3;

    private ?WhereItem $conditions = null;
    private bool $onlyMiddle = false;
    private bool $isLateral = false;
    private ?JoinType $type = null;

    private function __construct(
        private string|Select $target,
        private ?string $alias = null
    ) {
        if ($target === '' || $alias === '') {
            throw new RuntimeException("Bad join.");
        }
    }

    /**
     * Get a join target. A relation name, table or sub-query.
     * A relation name is in camelCase, a table is in CamelCase.
     */
    public function getTarget(): string|Select
    {
        return $this->target;
    }

    /**
     * Get an alias.
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * Get join conditions.
     */
    public function getConditions(): ?WhereItem
    {
        return $this->conditions;
    }

    /**
     * Is a sub-query join.
     */
    public function isSubQuery(): bool
    {
        return !is_string($this->target);
    }

    /**
     * Is a table join.
     */
    public function isTable(): bool
    {
        return is_string($this->target) && $this->target[0] === ucfirst($this->target[0]);
    }

    /**
     * Is a relation join.
     */
    public function isRelation(): bool
    {
        return !$this->isSubQuery() && !$this->isTable();
    }

    /**
     * Get a join mode.
     *
     * @return self::MODE_TABLE|self::MODE_RELATION|self::MODE_SUB_QUERY
     */
    public function getMode(): int
    {
        if ($this->isSubQuery()) {
            return self::MODE_SUB_QUERY;
        }

        if ($this->isRelation()) {
            return self::MODE_RELATION;
        }

        return self::MODE_TABLE;
    }

    /**
     * Is only middle table to be joined.
     */
    public function isOnlyMiddle(): bool
    {
        return $this->onlyMiddle;
    }

    /**
     * Is LATERAL.
     *
     * @since 9.1.6
     */
    public function isLateral(): bool
    {
        return $this->isLateral;
    }

    /**
     * Get a join type.
     *
     * @return JoinType|null
     *
     * @since 9.2.0
     */
    public function getType(): ?JoinType
    {
        return $this->type;
    }

    /**
     * Create.
     *
     * @param string|Select $target
     * A relation name, table or sub-query. A relation name should be in camelCase, a table in CamelCase.
     * When joining a table or sub-query, conditions should be specified.
     * When joining a relation, conditions will be applied automatically, additional conditions can
     * be specified as well.
     * @param ?string $alias An alias.
     */
    public static function create(string|Select $target, ?string $alias = null): self
    {
        return new self($target, $alias);
    }

    /**
     * Create with a table target.
     *
     * @param string $table A table name. Should start with an upper case letter.
     * @param ?string $alias An alias.
     */
    public static function createWithTableTarget(string $table, ?string $alias = null): self
    {
        return self::create(ucfirst($table), $alias);
    }

    /**
     * Create with a relation target. Conditions will be applied automatically.
     *
     * @param string $relation A relation name. Should start with a lower case letter.
     * @param ?string $alias An alias.
     */
    public static function createWithRelationTarget(string $relation, ?string $alias = null): self
    {
        return self::create(lcfirst($relation), $alias);
    }

    /**
     * Create with a sub-query.
     *
     * @param Select $subQuery A sub-query.
     * @param string $alias An alias.
     */
    public static function createWithSubQuery(Select $subQuery, string $alias): self
    {
        return new self($subQuery, $alias);
    }

    /**
     * Clone with an alias.
     */
    public function withAlias(?string $alias): self
    {
        $obj = clone $this;
        $obj->alias = $alias;

        return $obj;
    }

    /**
     * Clone with join conditions.
     */
    public function withConditions(?WhereItem $conditions): self
    {
        $obj = clone $this;
        $obj->conditions = $conditions;

        return $obj;
    }

    /**
     * Join only middle table. For many-to-many relationships.
     */
    public function withOnlyMiddle(bool $onlyMiddle = true): self
    {
        if (!$this->isRelation()) {
            throw new LogicException("Only-middle is compatible only with relation joins.");
        }

        $obj = clone $this;
        $obj->onlyMiddle = $onlyMiddle;

        return $obj;
    }

    /**
     * With LATERAL. Only for a sub-query join.
     *
     * @since 9.1.6
     */
    public function withLateral(bool $isLateral = true): self
    {
        if (!$this->isSubQuery()) {
            throw new LogicException("Lateral can be used only with sub-query joins.");
        }

        $obj = clone $this;
        $obj->isLateral = $isLateral;

        return $obj;
    }

    /**
     * With LEFT type.
     *
     * @since 9.2.0.
     */
    public function withLeft(): self
    {
        $obj = clone $this;
        $obj->type = JoinType::left;

        return $obj;
    }

    /**
     * With INNER type.
     *
     * @since 9.2.0.
     */
    public function withInner(): self
    {
        $obj = clone $this;
        $obj->type = JoinType::inner;

        return $obj;
    }

    /**
     * With a join type.
     *
     * @since 9.2.0.
     */
    public function withType(JoinType $type): self
    {
        $obj = clone $this;
        $obj->type = $type;

        return $obj;
    }
}
