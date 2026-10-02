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

use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Note;
use Espo\Entities\Note as NoteEntity;
use Espo\Entities\Preferences;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Tools\Stream\Service;

/**
 * @implements SaveHook<Note>
 * @noinspection PhpUnused
 */
class AfterCreate implements SaveHook
{
    public function __construct(
        private EntityManager $entityManager,
        private User $user,
        private Metadata $metadata,
        private Service $streamService
    ) {}

    public function process(Entity $entity): void
    {
        $parentType = $entity->getParentType();
        $parentId = $entity->getParentId();

        if (
            $entity->getType() !== NoteEntity::TYPE_POST ||
            !$parentType ||
            !$parentId
        ) {
            return;
        }

        if (!$this->metadata->get(['scopes', $parentType, 'stream'])) {
            return;
        }

        $preferences = $this->entityManager->getEntityById(Preferences::ENTITY_TYPE, $this->user->getId());

        if (!$preferences) {
            return;
        }

        if (!$preferences->get('followEntityOnStreamPost')) {
            return;
        }

        $parent = $this->entityManager->getEntityById($parentType, $parentId);

        if (!$parent || $this->user->isSystem() || $this->user->isApi()) {
            return;
        }

        $this->streamService->followEntity($parent, $this->user->getId());
    }
}
