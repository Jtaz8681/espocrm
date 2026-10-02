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

namespace Espo\Classes\RecordHooks\Note;

use Espo\Core\Acl;
use Espo\Core\Acl\Table as AclTable;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Note;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Tools\Stream\NoteUtil;

/**
 * @implements SaveHook<Note>
 * @noinspection PhpUnused
 */
class BeforeCreate implements SaveHook
{
    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl,
        private User $user,
        private NoteUtil $noteUtil,
        private Metadata $metadata,
    ) {}

    public function process(Entity $entity): void
    {
        $this->checkParent($entity);

        if (!$entity->isPost() && !$this->user->isAdmin()) {
            throw new Forbidden("Only 'Post' type allowed.");
        }

        if ($this->user->isPortal()) {
            $entity->setIsInternal(false);
        }

        if (
            !$entity->isPost() ||
            !$entity->getParentType() ||
            !$this->metadata->get("streamDefs.{$entity->getParentType()}.allowInternalNotes")
        ) {
            if ($entity->isInternal()) {
                throw new Forbidden("Internal note is not allowed.");
            }

            $entity->setIsInternal(false);
        }

        if ($entity->isPost()) {
            $this->noteUtil->handlePostText($entity);
        }

        $targetType = $entity->getTargetType();

        $entity->clear(Note::FIELD_IS_PINNED);
        $entity->clear('isGlobal');

        switch ($targetType) {
            case Note::TARGET_ALL:

                $entity->clear('usersIds');
                $entity->clear('teamsIds');
                $entity->clear('portalsIds');
                $entity->set('isGlobal', true);

                break;

            case Note::TARGET_SELF:

                $entity->clear('usersIds');
                $entity->clear('teamsIds');
                $entity->clear('portalsIds');
                $entity->setUsersIds([$this->user->getId()]);
                $entity->set('isForSelf', true);

                break;

            case Note::TARGET_USERS:

                $entity->clear('teamsIds');
                $entity->clear('portalsIds');

                break;

            case Note::TARGET_TEAMS:

                $entity->clear('usersIds');
                $entity->clear('portalsIds');

                break;

            case Note::TARGET_PORTALS:

                $entity->clear('usersIds');
                $entity->clear('teamsIds');

                break;
        }
    }

    /**
     * @throws Forbidden
     */
    private function checkParent(Note $entity): void
    {
        if (!$entity->getParentType() || !$entity->getParentId()) {
            return;
        }

        $parent = $this->entityManager->getEntityById($entity->getParentType(), $entity->getParentId());

        if ($parent && $this->acl->check($parent, AclTable::ACTION_READ)) {
            return;
        }

        throw new Forbidden("No access to parent.");
    }
}
