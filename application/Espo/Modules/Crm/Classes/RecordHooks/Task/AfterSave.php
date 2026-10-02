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

namespace Espo\Modules\Crm\Classes\RecordHooks\Task;

use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Task;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @implements SaveHook<Task>
 */
class AfterSave implements SaveHook
{
    public function __construct(
        private EntityManager $entityManager,
        private User $user,
    ) {}

    public function process(Entity $entity): void
    {
        /** @var ?string $emailId */
        $emailId = $entity->get('emailId');

        if (!$emailId || !$entity->getAssignedUser()) {
            return;
        }

        if (!$entity->isNew() && !$entity->isAttributeChanged('assignedUserId')) {
            return;
        }

        $email = $this->entityManager->getRDBRepositoryByClass(Email::class)->getById($emailId);

        if (!$email) {
            return;
        }

        $relation = $this->entityManager->getRelation($email, 'users');

        if ($relation->isRelatedById($entity->getAssignedUser()->getId())) {
            return;
        }

        $isRead = $entity->getAssignedUser()->getId() === $this->user->getId();

        $relation->relateById($entity->getAssignedUser()->getId(), [Email::USERS_COLUMN_IS_READ => $isRead]);
    }
}
