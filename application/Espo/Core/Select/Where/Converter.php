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

namespace Espo\Core\Select\Where;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Select\Where\Item\Type;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\Part\Where\Comparison;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\Part\WhereItem;
use Espo\ORM\Query\SelectBuilder;
use InvalidArgumentException;
use RuntimeException;

/**
 * Converts a search where (passed from front-end) to a where clause (for ORM).
 */
class Converter
{
    public function __construct(
        private ItemConverter $itemConverter,
        private Scanner $scanner
    ) {}

    /**
     * @throws BadRequest
     */
    public function convert(SelectBuilder $queryBuilder, Item $item, ?Converter\Params $params = null): WhereItem
    {
        if ($params && $params->useSubQueryIfMany() && $this->hasRelatedMany($queryBuilder, $item)) {
            return $this->convertSubQuery($queryBuilder, $item);
        }

        $whereClause = [];

        foreach ($this->itemToList($item) as $subItemRaw) {
            try {
                $subItem = Item::fromRaw($subItemRaw);
            } catch (InvalidArgumentException $e) {
                throw new BadRequest($e->getMessage());
            }

            $part = $this->processItem($queryBuilder, $subItem);

            if ($part === []) {
                continue;
            }

            $whereClause[] = $part;
        }

        $this->scanner->apply($queryBuilder, $item);

        return WhereClause::fromRaw($whereClause);
    }

    private function hasRelatedMany(SelectBuilder $queryBuilder, Item $item): bool
    {
        $entityType = $queryBuilder->build()->getFrom();

        if (!$entityType) {
            throw new RuntimeException("No 'from' in queryBuilder.");
        }

        return $this->scanner->hasRelatedMany($entityType, $item);
    }

    /**
     * @return array<int|string, mixed>
     * @throws BadRequest
     */
    private function itemToList(Item $item): array
    {
        if ($item->getType() !== Type::AND) {
            return [
                $item->getRaw(),
            ];
        }

        $list = $item->getValue();

        if (!is_array($list)) {
            throw new BadRequest("Bad where item value.");
        }

        return $list;
    }

    /**
     * @return array<int|string, mixed>
     * @throws BadRequest
     */
    private function processItem(SelectBuilder $queryBuilder, Item $item): array
    {
        return $this->itemConverter->convert($queryBuilder, $item)->getRaw();
    }

    /**
     * @throws BadRequest
     */
    private function convertSubQuery(SelectBuilder $queryBuilder, Item $item): Comparison
    {
        $entityType = $queryBuilder->build()->getFrom() ?? throw new RuntimeException();

        $subQueryBuilder = SelectBuilder::create()
            ->from($entityType)
            ->select(Attribute::ID);

        $subQueryBuilder->where(
            $this->convert($subQueryBuilder, $item)
        );

        return Cond::in(
            Expr::column(Attribute::ID),
            $subQueryBuilder->build()
        );
    }
}
