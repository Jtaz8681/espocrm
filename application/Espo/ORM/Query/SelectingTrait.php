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

namespace Espo\ORM\Query;

use Espo\ORM\Query\Part\Order;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\Part\Join;

use RuntimeException;

trait SelectingTrait
{
    /**
     * Get ORDER items.
     *
     * @return Order[]
     */
    public function getOrder(): array
    {
        return array_map(
            function ($item) {
                if (is_array($item) && count($item)) {
                    $itemValue = is_int($item[0]) ? (string) $item[0] : $item[0];

                    return Order::fromString($itemValue)
                        ->withDirection($item[1] ?? Order::ASC);
                }

                if (is_string($item)) {
                    return Order::fromString($item);
                }

                throw new RuntimeException("Bad order item.");
            },
            $this->params['orderBy'] ?? []
        );
    }

    /**
     * Get WHERE clause.
     */
    public function getWhere(): ?WhereClause
    {
        $whereClause = $this->params['whereClause'] ?? null;

        if ($whereClause === null || $whereClause === []) {
            return null;
        }

        $where = WhereClause::fromRaw($whereClause);

        if (!$where instanceof WhereClause) {
            throw new RuntimeException();
        }

        return $where;
    }

    /**
     * Get JOIN items.
     *
     * @return Join[]
     */
    public function getJoins(): array
    {
        return array_map(
            function ($item) {
                if (is_string($item)) {
                    $item = [$item];
                }

                $conditions = isset($item[2]) ?
                    WhereClause::fromRaw($item[2]) : null;

                $params = $item[3] ?? [];

                $type = $params['type'] ?? null;
                $type ??= Join\JoinType::inner;

                return Join::create($item[0])
                    ->withAlias($item[1] ?? null)
                    ->withConditions($conditions)
                    ->withType($type);
            },
            $this->params['joins'] ?? []
        );
    }

    /**
     * @return Join[]
     * @deprecated As of 9.2.0. Use getJoins and check join type.
     */
    public function getLeftJoins(): array
    {
        return array_map(
            function ($item) {
                if (is_string($item)) {
                    $item = [$item];
                }

                $conditions = isset($item[2]) ?
                    WhereClause::fromRaw($item[2]) : null;

                return Join::create($item[0])
                    ->withAlias($item[1] ?? null)
                    ->withConditions($conditions)
                    ->withLeft();
            },
            $this->params['leftJoins'] ?? []
        );
    }

    /**
     * @param array<string, mixed> $params
     */
    private static function validateRawParamsSelecting(array $params): void
    {
    }
}
