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

use Espo\Core\ORM\Repository\Option\RemoveOption;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Record\ActionHistory\Action;
use Espo\Core\Utils\Log;
use Espo\Entities\User;
use Espo\Core\Acl;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\MassAction\Data;
use Espo\Core\MassAction\MassAction;
use Espo\Core\MassAction\Params;
use Espo\Core\MassAction\QueryBuilder;
use Espo\Core\MassAction\Result;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Record\ServiceFactory;
use Exception;

class MassDelete implements MassAction
{
    public function __construct(
        private QueryBuilder $queryBuilder,
        private Acl $acl,
        private ServiceFactory $serviceFactory,
        private EntityManager $entityManager,
        private User $user,
        private Log $log,
    ) {}

    public function process(Params $params, Data $data): Result
    {
        $entityType = $params->getEntityType();

        if (!$this->acl->check($entityType, Acl\Table::ACTION_DELETE)) {
            throw new Forbidden("No delete access for '$entityType'.");
        }

        if (
            !$params->hasIds() &&
            $this->acl->getPermissionLevel(Acl\Permission::MASS_UPDATE) !== Acl\Table::LEVEL_YES
        ) {
            throw new Forbidden("No mass-update permission.");
        }

        $service = $this->serviceFactory->createForUser($entityType, $this->user);

        $repository = $this->entityManager->getRDBRepository($entityType);

        $query = $this->queryBuilder->build($params);

        $collection = $repository
            ->clone($query)
            ->sth()
            ->find();

        $ids = [];

        $count = 0;

        foreach ($collection as $entity) {
            if (!$this->acl->checkEntityDelete($entity)) {
                continue;
            }

            try {
                $repository->remove($entity, [
                    RemoveOption::MASS_REMOVE => true,
                    RemoveOption::MODIFIED_BY_ID => $this->user->getId(),
                    SaveOption::MASS_UPDATE => true, // Legacy.
                ]);
            } catch (Exception $e) {
                $this->log->info("Mass delete exception. Record: {id}.", [
                    'exception' => $e,
                    'id' => $entity->getId(),
                ]);

                continue;
            }

            $id = $entity->getId();

            $ids[] = $id;

            $count++;

            $service->processActionHistoryRecord(Action::DELETE, $entity);
        }

        return new Result($count, $ids);
    }
}
