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
use Espo\Core\Exceptions\Error\Body;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Utils\Config;
use Espo\Entities\Note;
use Espo\ORM\EntityManager;

/**
 * @noinspection PhpUnused
 */
class PostNotePin implements Action
{
    private const PINNED_MAX_COUNT = 5;

    public function __construct(
        private Config $config,
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

        $this->checkCanBePinned($note);
        $this->checkParent($note);
        $this->checkPinnedCount($note);

        $note->setIsPinned(true);
        $this->entityManager->saveEntity($note);

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
    private function checkPinnedCount(Note $entity): void
    {
        $maxCount = $this->config->get('notePinnedMaxCount') ?? self::PINNED_MAX_COUNT;

        $count = $this->entityManager
            ->getRDBRepositoryByClass(Note::class)
            ->where([
                'parentId' => $entity->getParentId(),
                'parentType' => $entity->getParentType(),
                'isPinned' => true,
            ])
            ->count();

        if ($count < $maxCount) {
            return;
        }

        throw Forbidden::createWithBody(
            'Pinned notes max count exceeded.',
            Body::create()->withMessageTranslation('pinnedMaxCountExceeded', 'Note', ['count' => (string) $count])
        );
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

    /**
     * @throws Forbidden
     */
    private function checkCanBePinned(Note $note): void
    {
        if (!$this->isEditableType($note)) {
            throw new Forbidden("Cannot pin note.");
        }
    }

    private function isEditableType(Note $entity): bool
    {
        return in_array($entity->getType(), [
            Note::TYPE_POST,
            Note::TYPE_EMAIL_RECEIVED,
            Note::TYPE_EMAIL_SENT,
        ]);
    }
}
