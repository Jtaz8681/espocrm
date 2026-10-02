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

use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\Part\Selection;
use Espo\ORM\Query\Part\Order;
use Espo\ORM\Query\Part\Expression;

use RuntimeException;

/**
 * Select parameters.
 *
 * Immutable.
 *
 * @todo Add validation and normalization.
 */
class Select implements SelectingQuery
{
    use SelectingTrait;
    use BaseTrait;

    public const ORDER_ASC = Order::ASC;
    public const ORDER_DESC = Order::DESC;

    /**
     * Get an entity type.
     */
    public function getFrom(): ?string
    {
        return $this->params['from'] ?? null;
    }

    /**
     * Get a from-alias
     */
    public function getFromAlias(): ?string
    {
        return $this->params['fromAlias'] ?? null;
    }

    /**
     * Get a from-query.
     */
    public function getFromQuery(): ?SelectingQuery
    {
        return $this->params['fromQuery'] ?? null;
    }

    /**
     * Get an OFFSET.
     */
    public function getOffset(): ?int
    {
        return $this->params['offset'] ?? null;
    }

    /**
     * Get a LIMIT.
     */
    public function getLimit(): ?int
    {
        return $this->params['limit'] ?? null;
    }

    /**
     * Get USE INDEX (list of indexes).
     *
     * @return string[]
     */
    public function getUseIndex(): array
    {
        return $this->params['useIndex'] ?? [];
    }

    /**
     * Get SELECT items.
     *
     * @return Selection[]
     */
    public function getSelect(): array
    {
        return array_map(
            function ($item) {
                if (is_array($item) && count($item)) {
                    return Selection::fromString($item[0])
                        ->withAlias($item[1] ?? null);
                }

                if (is_string($item)) {
                    return Selection::fromString($item);
                }

                throw new RuntimeException("Bad select item.");
            },
            $this->params['select'] ?? []
        );
    }

    /**
     * Whether DISTINCT is applied.
     */
    public function isDistinct(): bool
    {
        return $this->params['distinct'] ?? false;
    }

    /**
     * Whether a FOR SHARE lock mode is set.
     */
    public function isForShare(): bool
    {
        return $this->params['forShare'] ?? false;
    }

    /**
     * Whether a FOR UPDATE lock mode is set.
     */
    public function isForUpdate(): bool
    {
        return $this->params['forUpdate'] ?? false;
    }

    /**
     * Get GROUP BY items.
     *
     * @return Expression[]
     */
    public function getGroup(): array
    {
        return array_map(
            function (string $item) {
                return Expression::create($item);
            },
            $this->params['groupBy'] ?? []
        );
    }

    /**
     * Get HAVING clause.
     */
    public function getHaving(): ?WhereClause
    {
        $havingClause = $this->params['havingClause'] ?? null;

        if ($havingClause === null || $havingClause === []) {
            return null;
        }

        $having = WhereClause::fromRaw($havingClause);

        if (!$having instanceof WhereClause) {
            throw new RuntimeException();
        }

        return $having;
    }

    /**
     * @param array<string, mixed> $params
     */
    private function validateRawParams(array $params): void
    {
        $this->validateRawParamsSelecting($params);

        if (
            (
                !empty($params['joins']) ||
                !empty($params['leftJoins']) ||
                !empty($params['whereClause']) ||
                !empty($params['orderBy'])
            )
            &&
            empty($params['from']) && empty($params['fromQuery'])
        ) {
            throw new RuntimeException("Select params: Missing 'from'.");
        }
    }
}
