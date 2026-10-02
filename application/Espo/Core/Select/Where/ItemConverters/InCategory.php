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

namespace Espo\Core\Select\Where\ItemConverters;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Select\Where\Item;
use Espo\Core\Select\Where\ItemConverter;
use Espo\ORM\Defs;
use Espo\ORM\Entity;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\Part\WhereItem as WhereClauseItem;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;

/**
 * @noinspection PhpUnused
 */
class InCategory implements ItemConverter
{
    public function __construct(
        private string $entityType,
        private Defs $ormDefs
    ) {}

    public function convert(QueryBuilder $queryBuilder, Item $item): WhereClauseItem
    {
        $link = $item->getAttribute();
        $value = $item->getValue();

        if (!$link) {
            throw new BadRequest("No attribute.");
        }

        if ($value === null) {
            return WhereClause::create();
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $it) {
            if (!is_string($it) && !is_int($it)) {
                throw new BadRequest("Bad where item. Bad array item.");
            }
        }

        $entityDefs = $this->ormDefs->getEntity($this->entityType);

        if (!$entityDefs->hasRelation($link)) {
            throw new BadRequest("Not existing '$link' in where item.");
        }

        $defs = $entityDefs->getRelation($link);

        $path = lcfirst($defs->getForeignEntityType()) . 'Path';

        if ($defs->getType() === Entity::MANY_MANY) {
            $middle = ucfirst($defs->getRelationshipName());

            return Cond::in(
                Expr::column('id'),
                QueryBuilder::create()
                    ->from($middle, 'm')
                    ->select($defs->getMidKey())
                    ->join(
                        ucfirst($path),
                        $path,
                        ["$path.descendorId:" => "m.{$defs->getForeignMidKey()}"]
                    )
                    ->where(["$path.ascendorId" => $value])
                    ->build()
            );
        }

        if ($defs->getType() === Entity::BELONGS_TO) {
            $queryBuilder->join(
                ucfirst($path),
                $path,
                ["$path.descendorId:" => $defs->getKey()]
            );

            return WhereClause::fromRaw(["$path.ascendorId" => $value]);
        }

        throw new BadRequest("Not supported link '$link' in where item.");
    }
}
