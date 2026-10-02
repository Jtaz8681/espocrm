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

namespace Espo\Hooks\Common;

use Espo\Core\Field\Link;
use Espo\Core\Field\LinkMultiple;
use Espo\Core\Field\LinkMultipleItem;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements BeforeSave<Entity>
 */
class Collaborators implements BeforeSave
{
    public static int $order = 7;

    private const FIELD_COLLABORATORS = Field::COLLABORATORS;
    private const FIELD_ASSIGNED_USERS = Field::ASSIGNED_USERS;
    private const FIELD_ASSIGNED_USER = Field::ASSIGNED_USER;

    public function __construct(
        private Metadata $metadata,
    ) {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity instanceof CoreEntity) {
            return;
        }

        if (!$this->hasCollaborators($entity)) {
            return;
        }

        if ($entity->hasLinkMultipleField(self::FIELD_ASSIGNED_USERS)) {
            $this->processAssignedUsers($entity);

            return;
        }

        $this->processAssignedUser($entity);
    }

    private function hasCollaborators(CoreEntity $entity): bool
    {
        if (!$this->metadata->get("scopes.{$entity->getEntityType()}.collaborators")) {
            return false;
        }

        if (!$entity->hasLinkMultipleField(self::FIELD_COLLABORATORS)) {
            return false;
        }

        return true;
    }

    private function processAssignedUsers(CoreEntity $entity): void
    {
        if (!$entity->has(self::FIELD_COLLABORATORS . 'Ids')) {
            return;
        }

        $assignedUsers = $entity->getValueObject(self::FIELD_ASSIGNED_USERS);
        $collaborators = $entity->getValueObject(self::FIELD_COLLABORATORS);

        if (
            !$assignedUsers instanceof LinkMultiple ||
            !$collaborators instanceof LinkMultiple
        ) {
            return;
        }

        $countBefore = $collaborators->getCount();

        foreach ($assignedUsers->getList() as $assignedUser) {
            $collaborators = $collaborators->withAdded($assignedUser);
        }

        if ($countBefore === $collaborators->getCount()) {
            return;
        }

        $entity->setValueObject(self::FIELD_COLLABORATORS, $collaborators);
    }

    private function processAssignedUser(CoreEntity $entity): void
    {
        $idAttr = self::FIELD_ASSIGNED_USER . 'Id';

        if (!$entity->hasAttribute($idAttr) || !$entity->isAttributeChanged($idAttr)) {
            return;
        }

        $assignedUser = $entity->getValueObject(self::FIELD_ASSIGNED_USER);

        if (!$assignedUser instanceof Link) {
            return;
        }

        $collaborators = $entity->getValueObject(self::FIELD_COLLABORATORS);

        if (!$collaborators instanceof LinkMultiple) {
            return;
        }

        $collaborators = $collaborators
            ->withAdded(LinkMultipleItem::create($assignedUser->getId(), $assignedUser->getName()));

        $entity->setValueObject(self::FIELD_COLLABORATORS, $collaborators);
    }
}
