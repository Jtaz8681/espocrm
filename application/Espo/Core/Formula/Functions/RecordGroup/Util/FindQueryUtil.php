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

namespace Espo\Core\Formula\Functions\RecordGroup\Util;

use Espo\Core\Acl\SystemRestriction;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\BadArgumentValue;
use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\Exceptions\NotAllowedUsage;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\SelectBuilder;
use Espo\Core\Select\Where\Item;
use Espo\Core\Utils\Json;
use Espo\ORM\Query\Part\Order;
use Espo\ORM\QueryComposer\Util;
use InvalidArgumentException;
use stdClass;

class FindQueryUtil
{
    public function __construct(
        private SystemRestriction $systemRestriction,
    ) {}

    /**
     * @throws Error
     * @throws BadArgumentType
     */
    public function applyFilter(SelectBuilder $builder, mixed $filter, int $position): void
    {
        if (is_string($filter)) {
            $builder->withPrimaryFilter($filter);

            return;
        }

        if ($filter instanceof stdClass) {
            $rawWhere = Json::decode(Json::encode($filter), true);

            try {
                $where = Item::fromRaw($rawWhere);
            } catch (InvalidArgumentException $e) {
                throw new Error($e->getMessage(), 0, $e);
            }

            $builder->withWhere($where);

            return;
        }

        if ($filter) {
            throw BadArgumentType::create($position, 'string|object');
        }
    }

    /**
     * @throws BadArgumentValue
     */
    public function applyOrder(
        SelectBuilder $builder,
        ?string $orderBy,
        string|bool|null $order,
        int $orderPosition,
    ): void {

        if (is_bool($order)) {
            $order = $order ? Order::DESC : Order::ASC;
        }

        if (is_string($order)) {
            $order = strtoupper($order);

            if ($order !== Order::ASC && $order !== Order::DESC) {
                throw BadArgumentValue::create($orderPosition, "Order must be 'ASC'|'DESC'|bool|null.");
            }
        }

        if ($orderBy) {
            $builder->withSearchParams(
                SearchParams::create()
                    ->withOrderBy($orderBy)
                    ->withOrder($order)
            );

            return;
        }

        if ($order !== null) {
            $builder->withSearchParams(
                SearchParams::create()
                    ->withOrder($order)
            );
        }

        $builder->withDefaultOrder();
    }

    /**
     * @throws NotAllowedUsage
     */
    public function assertWhereClauseKeyValid(string $entityType, string $key): void
    {
        try {
            [$expression] = Util::splitWhereKeyThrowing($key);
        } catch (InvalidArgumentException) {
            throw new NotAllowedUsage("Not allowed where key expression '$key'");
        }

        if (Util::isComplexExpression($expression)) {
            throw new NotAllowedUsage("Not allowed expression is where key '$key'");
        }

        $attribute = $expression;

        if (!$this->systemRestriction->checkAttributeRead($entityType, $attribute)) {
            throw new NotAllowedUsage("Cannot use restricted attribute in where key '$key'.");
        }
    }
}
