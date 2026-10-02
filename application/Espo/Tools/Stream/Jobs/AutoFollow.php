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
use Espo\Core\Utils\Util;
use Espo\Entities\Note;
use Espo\ORM\EntityManager;
use Espo\Tools\Notification\HookProcessor\Params;
use Espo\Tools\Stream\Service as Service;
use Espo\Tools\Notification\Service as NotificationService;
use Espo\Entities\User;

/**
 * Handles auto-follow.
 */
class AutoFollow implements Job
{
    public function __construct(
        private Service $service,
        private NotificationService $notificationService,
        private AclManager $aclManager,
        private EntityManager $entityManager,
    ) {}

    public function run(Data $data): void
    {
        /** @var string[] $userIdList */
        $userIdList = $data->get('userIdList') ?? [];
        $entityType = $data->get('entityType');
        $entityId = $data->get('entityId');

        if (!$entityId || !$entityType) {
            return;
        }

        $entity = $this->entityManager->getEntityById($entityType, $entityId);

        if (!$entity) {
            return;
        }

        foreach ($userIdList as $i => $userId) {
            $user = $this->entityManager->getRDBRepositoryByClass(User::class)->getById($userId);

            if (!$user) {
                unset($userIdList[$i]);

                continue;
            }

            try {
                $hasAccess = $this->aclManager->checkEntityStream($user, $entity);
            } catch (AclNotImplemented) {
                $hasAccess = false;
            }

            if (!$hasAccess) {
                unset($userIdList[$i]);
            }
        }

        $userIdList = array_values($userIdList);

        foreach ($userIdList as $i => $userId) {
            if ($this->service->checkIsFollowed($entity, $userId)) {
                unset($userIdList[$i]);
            }
        }

        $userIdList = array_values($userIdList);

        if (!count($userIdList)) {
            return;
        }

        $this->service->followEntityMass($entity, $userIdList);

        $notes = $this->entityManager
            ->getRDBRepositoryByClass(Note::class)
            ->where([
                'parentType' => $entityType,
                'parentId' => $entityId,
            ])
            ->order('number')
            ->find();

        // Group all notifications.
        $params = new Params(Util::generateId());

        foreach ($notes as $note) {
            $this->notificationService->notifyAboutNote($userIdList, $note, $params);
        }
    }
}
