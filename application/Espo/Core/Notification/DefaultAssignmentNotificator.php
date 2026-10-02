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

namespace Espo\Core\Notification;

use Espo\Core\Field\LinkParent;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Entities\User;
use Espo\Entities\Notification;
use Espo\Core\Notification\AssignmentNotificator\Params;

/**
 * @implements AssignmentNotificator<Entity>
 */
class DefaultAssignmentNotificator implements AssignmentNotificator
{
    public const OPTION_FORCE_ASSIGNED_USER = 'forceAssignedUser';

    private const FIELD_ASSIGNED_USERS = Field::ASSIGNED_USERS;
    private const ATTR_ASSIGNED_USER_ID = 'assignedUserId';

    public function __construct(
        protected User $user,
        protected EntityManager $entityManager,
        protected UserEnabledChecker $userChecker
    ) {}

    public function process(Entity $entity, Params $params): void
    {
        if (!$entity instanceof CoreEntity) {
            return;
        }

        if (
            $entity->hasLinkMultipleField(self::FIELD_ASSIGNED_USERS) &&
            !$params->getOption(self::OPTION_FORCE_ASSIGNED_USER)
        ) {
            $userIds = $entity->getLinkMultipleIdList(self::FIELD_ASSIGNED_USERS);
            /** @var string[] $fetchedIds */
            $fetchedIds = $entity->getFetched(self::FIELD_ASSIGNED_USERS . 'Ids') ?? [];

            foreach ($userIds as $userId) {
                if (in_array($userId, $fetchedIds)) {
                    continue;
                }

                $this->processForUser($entity, $userId);
            }

            return;
        }

        if (!$entity->get(self::ATTR_ASSIGNED_USER_ID)) {
            return;
        }

        if (!$entity->isAttributeChanged(self::ATTR_ASSIGNED_USER_ID)) {
            return;
        }

        $assignedUserId = $entity->get(self::ATTR_ASSIGNED_USER_ID);

        $this->processForUser($entity, $assignedUserId);
    }

    protected function processForUser(Entity $entity, string $assignedUserId): void
    {
        if (!$this->userChecker->checkAssignment($entity->getEntityType(), $assignedUserId)) {
            return;
        }

        if ($entity->hasAttribute('createdById') && $entity->hasAttribute('modifiedById')) {
            $isSelfAssignment = $entity->isNew() ?
                $assignedUserId === $entity->get('createdById') :
                $assignedUserId === $entity->get('modifiedById');

            if ($isSelfAssignment) {
                return;
            }
        }

        $isSelfAssignment = $assignedUserId === $this->user->getId();

        if ($isSelfAssignment) {
            return;
        }

        $notification = $this->entityManager->getRDBRepositoryByClass(Notification::class)->getNew();

        $notification
            ->setType(Notification::TYPE_ASSIGN)
            ->setUserId($assignedUserId)
            ->setData([
                'entityType' => $entity->getEntityType(),
                'entityId' => $entity->getId(),
                'relatedName' => $entity->get(Field::NAME),
                'isNew' => $entity->isNew(),
                'userId' => $this->user->getId(),
                'userName' => $this->user->getName(),
            ])
            ->setRelated(LinkParent::fromEntity($entity));

        $this->entityManager->saveEntity($notification);
    }
}
