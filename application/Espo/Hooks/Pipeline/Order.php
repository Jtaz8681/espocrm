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

namespace Espo\Hooks\Pipeline;

use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Entities\Pipeline;
use Espo\Tools\Pipeline\MoveService;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\Part\Order as OrderPart;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements BeforeSave<Pipeline>
 * @implements AfterRemove<Pipeline>
 */
class Order implements BeforeSave, AfterRemove
{
    public function __construct(
        private EntityManager $entityManager,
        private MoveService $moveService,
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isNew()) {
            return;
        }

        $entityType = $entity->getTargetEntityType();

        $query = SelectBuilder::create()
            ->from(Pipeline::ENTITY_TYPE)
            ->select(Expr::max(Expr::column(Pipeline::FIELD_ORDER)), 'max')
            ->select(Attribute::ID)
            ->group(Attribute::ID)
            ->limit(0, 1)
            ->order(Expr::max(Expr::column(Pipeline::FIELD_ORDER)), OrderPart::DESC)
            ->where([
                Pipeline::FIELD_ENTITY_TYPE => $entityType,
            ])
            ->build();

        $sth = $this->entityManager->getQueryExecutor()->execute($query);

        $row = $sth->fetch();

        $order = $row ? $row['max'] : 0;
        $order ++;

        $entity->set(Pipeline::FIELD_ORDER, $order);
    }

    public function afterRemove(Entity $entity, RemoveOptions $options): void
    {
        $this->moveService->reOrder($entity::class);
    }
}
