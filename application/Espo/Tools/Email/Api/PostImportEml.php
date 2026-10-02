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

namespace Espo\Tools\Email\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Entities\Attachment;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Espo\Tools\Email\ImportEmlService;

/**
 * @noinspection PhpUnused
 */
class PostImportEml implements Action
{
    private const string RELATED_TYPE = 'ImportEml';

    public function __construct(
        private Acl $acl,
        private User $user,
        private ImportEmlService $service,
        private EntityManager $entityManager,
    ) {}

    public function process(Request $request): Response
    {
        $this->checkAccess();

        $fileId = $request->getParsedBody()->fileId ?? null;

        if (!is_string($fileId)) {
            throw new BadRequest("No 'fileId'.");
        }

        $attachment = $this->getAttachment($fileId);

        $email = $this->service->import($attachment, $this->user->getId());

        return ResponseComposer::json(['id' => $email->getId()]);
    }

    /**
     * @throws NotFound
     * @throws Forbidden
     */
    private function getAttachment(string $fileId): Attachment
    {
        $attachment = $this->entityManager->getRDBRepositoryByClass(Attachment::class)->getById($fileId);

        if (!$attachment) {
            throw new NotFound("Attachment not found.");
        }

        if (!$this->acl->checkEntityRead($attachment)) {
            throw new Forbidden("No access to attachment.");
        }

        if ($attachment->getCreatedBy()?->getId() !== $this->user->getId()) {
            throw new Forbidden("Attachment is not owned.");
        }

        if ($attachment->getRelatedType() !== self::RELATED_TYPE) {
            throw new Forbidden("Attachment is not for import EML.");
        }

        return $attachment;
    }

    /**
     * @throws Forbidden
     */
    private function checkAccess(): void
    {
        if (!$this->acl->checkScope(Email::ENTITY_TYPE, Acl\Table::ACTION_CREATE)) {
            throw new Forbidden("No 'create' access.");
        }

        if (!$this->acl->checkScope('Import')) {
            throw new Forbidden("No access to 'Import'.");
        }
    }
}
