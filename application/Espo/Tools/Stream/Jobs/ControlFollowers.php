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

namespace Espo\Tools\Stream\Jobs;

use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data;

use Espo\Core\AclManager;
use Espo\Core\Acl\Exceptions\NotImplemented as AclNotImplemented;

use Espo\ORM\EntityManager;

use Espo\ORM\Name\Attribute;
use Espo\Tools\Stream\Service as Service;
use Espo\Entities\User;

/**
 * Unfollows users that don't have access.
 */
class ControlFollowers implements Job
{

    public function __construct(
        private Service $service,
        private AclManager $aclManager,
        private EntityManager $entityManager
    ) {}

    public function run(Data $data): void
    {
        $entityType = $data->get('entityType');
        $entityId = $data->get('entityId');

        if (!$entityId || !$entityType) {
            return;
        }

        $entity = $this->entityManager->getEntityById($entityType, $entityId);

        if (!$entity) {
            return;
        }

        $idList = $this->service->getEntityFollowerIdList($entity);

        $userList = $this->entityManager
            ->getRDBRepository(User::ENTITY_TYPE)
            ->where([Attribute::ID => $idList])
            ->find();

        foreach ($userList as $user) {
            /** @var string $userId */
            $userId = $user->getId();

            if (!$user->isActive()) {
                $this->service->unfollowEntity($entity, $userId);

                continue;
            }

            if ($user->isPortal()) {
                continue;
            }

            try {
                $hasAccess = $this->aclManager->checkEntityStream($user, $entity);
            } catch (AclNotImplemented) {
                $hasAccess = false;
            }

            if ($hasAccess) {
                continue;
            }

            $this->service->unfollowEntity($entity, $userId);
        }
    }
}
