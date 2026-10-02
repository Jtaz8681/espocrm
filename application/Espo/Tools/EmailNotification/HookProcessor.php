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

namespace Espo\Tools\EmailNotification;

use Espo\Core\Name\Field;
use Espo\ORM\Entity;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\Utils\Config;
use Espo\Core\ApplicationState;
use Espo\Core\Job\QueueName;
use Espo\Core\Job\JobSchedulerFactory;
use Espo\Tools\EmailNotification\Jobs\NotifyAboutAssignment;

class HookProcessor
{
    public function __construct(
        private Config $config,
        private ApplicationState $applicationState,
        private JobSchedulerFactory $jobSchedulerFactory
    ) {}

    public function afterSave(Entity $entity): void
    {
        if (!$entity instanceof CoreEntity) {
            return;
        }

        if (!$this->checkToProcess($entity)) {
            return;
        }

        if ($entity->has('assignedUsersIds')) {
            $this->processMultiple($entity);

            return;
        }

        $userId = $entity->get('assignedUserId');

        if (
            !$userId ||
            !$entity->isAttributeChanged('assignedUserId') ||
            !$this->isNotSelfAssignment($entity, $userId)
        ) {
            return;
        }

        $this->createJob($entity, $userId);
    }

    private function processMultiple(CoreEntity $entity): void
    {
        $userIdList = $entity->getLinkMultipleIdList(Field::ASSIGNED_USERS);
        $fetchedAssignedUserIdList = $entity->getFetched(Field::ASSIGNED_USERS . 'Ids') ?? [];

        foreach ($userIdList as $userId) {
            if (
                in_array($userId, $fetchedAssignedUserIdList) ||
                !$this->isNotSelfAssignment($entity, $userId)
            ) {
                continue;
            }

            $this->createJob($entity, $userId);
        }
    }

    private function checkToProcess(CoreEntity $entity): bool
    {
        if (!$this->config->get('assignmentEmailNotifications')) {
            return false;
        }

        $hasAssignedUserField =
            $entity->has('assignedUserId') ||
            $entity->hasLinkMultipleField(Field::ASSIGNED_USERS) &&
            $entity->has('assignedUsersIds');

        if (!$hasAssignedUserField) {
            return false;
        }

        return in_array(
            $entity->getEntityType(),
            $this->config->get('assignmentEmailNotificationsEntityList') ?? []
        );
    }

    private function isNotSelfAssignment(Entity $entity, string $assignedUserId): bool
    {
        if ($entity->hasAttribute('createdById') && $entity->hasAttribute('modifiedById')) {
            if ($entity->isNew()) {
                return $assignedUserId !== $entity->get('createdById');
            }

            return $assignedUserId !== $entity->get('modifiedById');
        }

        return $assignedUserId !== $this->applicationState->getUserId();
    }

    private function createJob(Entity $entity, string $userId): void
    {
        $this->jobSchedulerFactory
            ->create()
            ->setClassName(NotifyAboutAssignment::class)
            ->setQueue(QueueName::E0)
            ->setData([
                'userId' => $userId,
                'assignerUserId' => $this->applicationState->getUserId(),
                'entityId' => $entity->getId(),
                'entityType' => $entity->getEntityType(),
            ])
            ->schedule();
    }
}
