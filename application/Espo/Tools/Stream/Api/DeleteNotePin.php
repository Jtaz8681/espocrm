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

namespace Espo\Tools\Stream\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Entities\Note;
use Espo\ORM\EntityManager;

/**
 * @noinspection PhpUnused
 */
class DeleteNotePin implements Action
{
    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl
    ) {}

    /**
     * @inheritDoc
     */
    public function process(Request $request): Response
    {
        $id = $request->getRouteParam('id');

        if (!$id) {
            throw new BadRequest();
        }

        $note = $this->getNote($id);

        $this->checkParent($note);

        if ($note->isPinned()) {
            $note->setIsPinned(false);
            $this->entityManager->saveEntity($note);
        }

        return ResponseComposer::json(true);
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    private function getNote(string $id): Note
    {
        $note = $this->entityManager->getRDBRepositoryByClass(Note::class)->getById($id);

        if (!$note) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityRead($note)) {
            throw new Forbidden("No read access.");
        }

        return $note;
    }

    /**
     * @throws Forbidden
     */
    private function checkParent(Note $note): void
    {
        if (!$note->getParentType() || !$note->getParentId()) {
            throw new Forbidden("No parent.");
        }

        $parent = $this->entityManager->getEntityById($note->getParentType(), $note->getParentId());

        if (!$parent) {
            throw new Forbidden("Parent not found.");
        }

        if (!$this->acl->checkEntityEdit($parent)) {
            throw new Forbidden("No parent edit access.");
        }
    }
}
