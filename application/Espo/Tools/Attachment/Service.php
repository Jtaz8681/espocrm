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

namespace Espo\Tools\Attachment;

use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\ServiceContainer;
use Espo\Entities\Attachment;
use Espo\ORM\EntityManager;
use Espo\Repositories\Attachment as AttachmentRepository;

class Service
{
    private ServiceContainer $recordServiceContainer;
    private EntityManager $entityManager;
    private AccessChecker $accessChecker;

    public function __construct(
        ServiceContainer $recordServiceContainer,
        EntityManager $entityManager,
        AccessChecker $accessChecker
    ) {
        $this->recordServiceContainer = $recordServiceContainer;
        $this->entityManager = $entityManager;
        $this->accessChecker = $accessChecker;
    }

    /**
     * Get file data (for downloading).
     *
     * @throws NotFound
     * @throws Forbidden
     */
    public function getFileData(string $id): FileData
    {
        /** @var ?Attachment $attachment */
        $attachment = $this->recordServiceContainer
            ->get(Attachment::ENTITY_TYPE)
            ->getEntity($id);

        if (!$attachment) {
            throw new NotFound();
        }

        return new FileData(
            $attachment->getName(),
            $attachment->getType(),
            $this->getAttachmentRepository()->getStream($attachment),
            $this->getAttachmentRepository()->getSize($attachment)
        );
    }

    /**
     * Copy an attachment record (to reuse the same file w/o copying it in the storage).
     *
     * @throws Forbidden
     * @throws NotFound
     */
    public function copy(string $id, FieldData $data): Attachment
    {
        $this->accessChecker->check($data);

        /** @var ?Attachment $attachment */
        $attachment = $this->recordServiceContainer
            ->get(Attachment::ENTITY_TYPE)
            ->getEntity($id);

        if (!$attachment) {
            throw new NotFound();
        }

        $copied = $this->getAttachmentRepository()->getCopiedAttachment($attachment);

        $copied->set('parentType', $data->getParentType());
        $copied->set('relatedType', $data->getRelatedType());
        $copied->setTargetField($data->getField());
        $copied->setRole(Attachment::ROLE_ATTACHMENT);

        $this->getAttachmentRepository()->save($copied);

        return $copied;
    }

    private function getAttachmentRepository(): AttachmentRepository
    {
        /** @var AttachmentRepository */
        return $this->entityManager->getRepositoryByClass(Attachment::class);
    }
}
