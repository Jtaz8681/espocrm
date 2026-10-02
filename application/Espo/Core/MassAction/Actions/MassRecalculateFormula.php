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

namespace Espo\Core\MassAction\Actions;

use Espo\Core\ApplicationUser;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\MassAction\Data;
use Espo\Core\MassAction\MassAction;
use Espo\Core\MassAction\Params;
use Espo\Core\MassAction\QueryBuilder;
use Espo\Core\MassAction\Result;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\SystemUser;
use Espo\Entities\User;

class MassRecalculateFormula implements MassAction
{
    public function __construct(
        private QueryBuilder $queryBuilder,
        private EntityManager $entityManager,
        private User $user,
        private SystemUser $systemUser
    ) {}

    public function process(Params $params, Data $data): Result
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }

        $entityType = $params->getEntityType();

        $query = $this->queryBuilder->build($params);

        $collection = $this->entityManager
            ->getRDBRepository($entityType)
            ->clone($query)
            ->sth()
            ->find();

        $ids = [];

        $count = 0;

        foreach ($collection as $entity) {
            $this->entityManager->saveEntity($entity, [
                'modifiedById' => $this->systemUser->getId(),
            ]);

            /** @var string $id */
            $id = $entity->getId();

            $ids[] = $id;
            $count++;
        }

        return new Result($count, $ids);
    }
}
