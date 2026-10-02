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

namespace Espo\Classes\Acl\Attachment;

use Espo\Core\Name\Field;
use Espo\Entities\Attachment;
use Espo\Entities\Note;
use Espo\Entities\Settings;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Core\Acl\AccessEntityCREDChecker;
use Espo\Core\Acl\DefaultAccessChecker;
use Espo\Core\Acl\ScopeData;
use Espo\Core\Acl\Traits\DefaultAccessCheckerDependency;
use Espo\Core\AclManager;
use Espo\Core\ORM\EntityManager;

/**
 * @implements AccessEntityCREDChecker<Attachment>
 */
class AccessChecker implements AccessEntityCREDChecker
{
    use DefaultAccessCheckerDependency;

    public function __construct(
        DefaultAccessChecker $defaultAccessChecker,
        private AclManager $aclManager,
        private EntityManager $entityManager
    ) {
        $this->defaultAccessChecker = $defaultAccessChecker;
    }

    public function checkEntityRead(User $user, Entity $entity, ScopeData $data): bool
    {
        if ($entity->getParentType() === Settings::ENTITY_TYPE) {
            // Allow the logo.
            return true;
        }

        $parent = null;

        $parentType = $entity->getParentType();
        $parentId = $entity->getParent()?->getId();

        $relatedType = $entity->getRelatedType();
        $relatedId = $entity->getRelated()?->getId();

        if ($parentId && $parentType) {
            $parent = $this->entityManager->getEntityById($parentType, $parentId);
        } else if ($relatedId && $relatedType) {
            $parent = $this->entityManager->getEntityById($relatedType, $relatedId);
        }

        if (!$parent) {
            if ($this->defaultAccessChecker->checkEntityRead($user, $entity, $data)) {
                return true;
            }

            return false;
        }

        if ($parent->getEntityType() === Note::ENTITY_TYPE) {
            /** @var Note $parent */
            $result = $this->checkEntityReadNoteParent($user, $parent);

            if ($result !== null) {
                return $result;
            }
        } else if ($this->aclManager->checkEntity($user, $parent)) {
            if (
                $entity->getTargetField() &&
                !$this->aclManager->checkField($user, $parent->getEntityType(), $entity->getTargetField())
            ) {
                return false;
            }

            return true;
        }

        if ($this->defaultAccessChecker->checkEntityRead($user, $entity, $data)) {
            return true;
        }

        return false;
    }

    private function checkEntityReadNoteParent(User $user, Note $note): ?bool
    {
        if ($note->getTargetType() === Note::TARGET_TEAMS) {
            $intersect = array_intersect(
                $note->getLinkMultipleIdList(Field::TEAMS),
                $user->getLinkMultipleIdList(Field::TEAMS)
            );

            if (count($intersect)) {
                return true;
            }

            return null;
        }

        if ($note->getTargetType() === Note::TARGET_USERS) {
            $isRelated = $this->entityManager
                ->getRDBRepository(Note::ENTITY_TYPE)
                ->getRelation($note, 'users')
                ->isRelated($user);

            if ($isRelated) {
                return true;
            }

            return null;
        }

        if ($note->getTargetType() === Note::TARGET_ALL) {
            return true;
        }

        if (!$note->getParentId() || !$note->getParentType()) {
            return null;
        }

        $parent = $this->entityManager->getEntityById($note->getParentType(), $note->getParentId());

        if ($parent && $this->aclManager->checkEntity($user, $parent)) {
            return true;
        }

        return null;
    }
}
