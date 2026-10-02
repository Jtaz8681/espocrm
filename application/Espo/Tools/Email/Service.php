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

namespace Espo\Tools\Email;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\ServiceContainer;
use Espo\Entities\Attachment;
use Espo\Entities\Email;
use Espo\ORM\EntityManager;
use Espo\Repositories\Attachment as AttachmentRepository;
use Espo\Tools\Attachment\AccessChecker as AttachmentAccessChecker;
use Espo\Tools\Attachment\FieldData;

class Service
{
    private EntityManager $entityManager;
    private AttachmentAccessChecker $attachmentAccessChecker;
    private ServiceContainer $serviceContainer;

    public function __construct(
        EntityManager $entityManager,
        AttachmentAccessChecker $attachmentAccessChecker,
        ServiceContainer $serviceContainer
    ) {
        $this->entityManager = $entityManager;
        $this->attachmentAccessChecker = $attachmentAccessChecker;
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * Copy email attachments for re-using (e.g. in a forward email).
     *
     * @return Attachment[]
     * @throws NotFound
     * @throws Forbidden
     */
    public function copyAttachments(string $id, FieldData $fieldData): array
    {
        /** @var ?Email $entity */
        $entity = $this->serviceContainer
            ->get(Email::ENTITY_TYPE)
            ->getEntity($id);

        if (!$entity) {
            throw new NotFound();
        }

        // It does not check field-level access to the email's attachments field.
        // Do not treat it as a problem when scanning.

        $this->attachmentAccessChecker->check($fieldData);

        $list = [];

        foreach ($entity->getAttachmentIdList() as $attachmentId) {
            $attachment = $this->copyAttachment($attachmentId, $fieldData);

            if ($attachment) {
                $list[] = $attachment;
            }
        }

        return $list;
    }

    private function copyAttachment(string $attachmentId, FieldData $fieldData): ?Attachment
    {
        /** @var ?Attachment $attachment */
        $attachment = $this->entityManager
            ->getRDBRepositoryByClass(Attachment::class)
            ->getById($attachmentId);

        if (!$attachment) {
            return null;
        }

        $copied = $this->getAttachmentRepository()->getCopiedAttachment($attachment);

        $copied->set('parentType', $fieldData->getParentType());
        $copied->set('relatedType', $fieldData->getRelatedType());
        $copied->setTargetField($fieldData->getField());
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
