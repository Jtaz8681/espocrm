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

namespace Espo\ORM;

use Espo\ORM\Query\Delete;
use Espo\ORM\Query\DeleteBuilder;
use Espo\ORM\Query\Insert;
use Espo\ORM\Query\InsertBuilder;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\Part\Selection;
use Espo\ORM\Query\Query;
use Espo\ORM\Query\Select;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Query\Union;
use Espo\ORM\Query\UnionBuilder;
use Espo\ORM\Query\Update;
use Espo\ORM\Query\UpdateBuilder;

use RuntimeException;

/**
 * Creates query builders for specific query types.
 */
class QueryBuilder
{
    /**
     * Specify SELECT. Columns and expressions to be selected. If not called, then
     * all entity attributes will be selected. Passing an array will reset
     * previously set items. Passing a SelectExpression|Expression|string will append the item.
     *
     * Usage options:
     * * `select(SelectExpression $expression)`
     * * `select([$expr1, $expr2, ...])`
     * * `select(string $expression, string $alias)`
     *
     * @param Selection|Selection[]|Expression|string[]|string $select
     * An array of expressions or one expression.
     * @param ?string $alias An alias. Actual if the first parameter is not an array.
     */
    public function select($select = null, ?string $alias = null): SelectBuilder
    {
        $builder = new SelectBuilder();

        if ($select === null) {
            return $builder;
        }

        return $builder->select($select, $alias);
    }

    /**
     * Proceed with UPDATE builder.
     */
    public function update(): UpdateBuilder
    {
        return new UpdateBuilder();
    }

    /**
     * Proceed with DELETE builder.
     */
    public function delete(): DeleteBuilder
    {
        return new DeleteBuilder();
    }

    /**
     * Proceed with INSERT builder.
     */
    public function insert(): InsertBuilder
    {
        return new InsertBuilder();
    }

    /**
     * Proceed with UNION builder.
     */
    public function union(): UnionBuilder
    {
        return new UnionBuilder();
    }

    /**
     * Clone an existing query and proceed modifying it.
     *
     * @return SelectBuilder|UpdateBuilder|DeleteBuilder|InsertBuilder|UnionBuilder
     * @throws RuntimeException
     */
    public function clone(Query $query): SelectBuilder|UpdateBuilder|DeleteBuilder|InsertBuilder|UnionBuilder
    {
        if ($query instanceof Select) {
            return $this->select()->clone($query);
        }

        if ($query instanceof Update) {
            return $this->update()->clone($query);
        }

        if ($query instanceof Delete) {
            return $this->delete()->clone($query);
        }

        if ($query instanceof Insert) {
            return $this->insert()->clone($query);
        }

        if ($query instanceof Union) {
            return $this->union()->clone($query);
        }

        throw new RuntimeException("Can't clone an unsupported query.");
    }
}
