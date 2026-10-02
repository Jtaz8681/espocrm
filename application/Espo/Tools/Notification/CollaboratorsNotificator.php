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

namespace Espo\Tools\Notification;

use Espo\Core\Acl\AssignmentChecker\Helper;
use Espo\Core\Field\LinkParent;
use Espo\Core\Name\Field;
use Espo\Core\Notification\AssignmentNotificator\Params;
use Espo\Core\Notification\UserEnabledChecker;
use Espo\Core\ORM\Entity;
use Espo\Entities\Notification;
use Espo\Entities\User;
use Espo\ORM\EntityManager;

class CollaboratorsNotificator
{
    public function __construct(
        private Helper $helper,
        private EntityManager $entityManager,
        private User $user,
        private UserEnabledChecker $userEnabledChecker,
    ) {}

    public function process(Entity $entity, Params $params): void
    {
        if (!$this->toProcess($entity)) {
            return;
        }

        $userIds = $entity->getLinkMultipleIdList(Field::COLLABORATORS);
        $previousUserIds = $entity->getFetchedLinkMultipleIdList(Field::COLLABORATORS);

        $addedUserIds = array_diff($userIds, $previousUserIds);

        foreach ($addedUserIds as $userId) {
            $this->processForUser($entity, $userId, $params);
        }
    }

    private function processForUser(Entity $entity, string $userId, Params $params): void
    {
        if (!$this->toProcessUser($entity, $userId)) {
            return;
        }

        $notification = $this->entityManager->getRDBRepositoryByClass(Notification::class)->getNew();

        $notification
            ->setType(Notification::TYPE_COLLABORATING)
            ->setUserId($userId)
            ->setData([
                'relatedName' => $entity->get(Field::NAME),
                'createdByName' => $this->user->getName(),
            ])
            ->setRelated(LinkParent::fromEntity($entity))
            // Needed for grouping.
            ->setRelatedParent(LinkParent::fromEntity($entity))
            ->setActionId($params->getActionId());

        $this->entityManager->saveEntity($notification);
    }

    private function toProcessUser(Entity $entity, string $userId): bool
    {
        $entityType = $entity->getEntityType();

        if ($userId === $this->user->getId()) {
            return false;
        }

        if ($this->helper->hasAssignedUsersField($entityType)) {
            return !in_array($userId, $entity->getLinkMultipleIdList(Field::ASSIGNED_USERS));
        }

        if ($this->helper->hasAssignedUserField($entityType)) {
            return $userId !== $entity->get(Field::ASSIGNED_USER . 'Id');
        }

        if (!$this->userEnabledChecker->checkAssignment($entityType, $userId)) {
            return false;
        }

        return true;
    }

    private function toProcess(Entity $entity): bool
    {
        if (!$this->helper->hasCollaboratorsField($entity->getEntityType())) {
            return false;
        }

        $idsAttr = Field::COLLABORATORS . 'Ids';

        if (!$entity->isAttributeChanged($idsAttr)) {
            return false;
        }

        return true;
    }

}
