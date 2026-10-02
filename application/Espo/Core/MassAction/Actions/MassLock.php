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

use Espo\Core\Acl;
use Espo\Core\Exceptions\ErrorSilent;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\MassAction\Data;
use Espo\Core\MassAction\MassAction;
use Espo\Core\MassAction\Params;
use Espo\Core\MassAction\QueryBuilder;
use Espo\Core\MassAction\Result;
use Espo\ORM\EntityManager;
use Espo\Tools\Lock\LockService;

/**
 * @noinspection PhpUnused
 */
class MassLock implements MassAction
{
    public function __construct(
        private QueryBuilder $queryBuilder,
        private LockService $lockService,
        private EntityManager $entityManager,
        private Acl $acl,
    ) {}

    public function process(Params $params, Data $data): Result
    {
        $entityType = $params->getEntityType();

        if (!$this->lockService->isEnabled($entityType)) {
            throw new ErrorSilent("Not enabled.");
        }

        if (!$this->lockService->isAllowed($entityType)) {
            throw new ForbiddenSilent("Not allowed.");
        }

        if ($this->acl->getPermissionLevel(Acl\Permission::MASS_UPDATE) !== Acl\Table::LEVEL_YES) {
            throw new ForbiddenSilent("No mass update permission.");
        }

        $query = $this->queryBuilder->build($params);

        $collection = $this->entityManager
            ->getRDBRepository($entityType)
            ->clone($query)
            ->sth()
            ->find();

        return $this->lockService->massLock($collection);
    }
}
