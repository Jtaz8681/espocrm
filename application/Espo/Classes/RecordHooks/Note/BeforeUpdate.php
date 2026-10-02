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

use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\Note;
use Espo\ORM\Entity;
use Espo\Tools\Stream\NoteUtil;

/**
 * @implements SaveHook<Note>
 * @noinspection PhpUnused
 */
class BeforeUpdate implements SaveHook
{
    public function __construct(
        private NoteUtil $noteUtil,
    ) {}

    public function process(Entity $entity): void
    {
        if (!$this->isEditableType($entity)) {
            throw new ForbiddenSilent("Note is not editable.");
        }

        if ($entity->isPost()) {
            $this->noteUtil->handlePostText($entity);
        }

        if (!$entity->isPost()) {
            $entity->clear('post');
            $entity->clear('attachmentsIds');
        }
    }

    private function isEditableType(Note $entity): bool
    {
        return $entity->getType() == Note::TYPE_POST;
    }
}
