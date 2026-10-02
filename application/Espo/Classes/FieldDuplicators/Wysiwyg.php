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

namespace Espo\Classes\FieldDuplicators;

use Espo\Core\Acl;
use Espo\Core\Record\Duplicator\FieldDuplicator;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Repositories\Attachment as AttachmentRepository;
use Espo\Entities\Attachment;

use stdClass;

/**
 * @noinspection PhpUnused
 */
class Wysiwyg implements FieldDuplicator
{
    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl,
        private Metadata $metadata,
    ) {}

    public function duplicate(Entity $entity, string $field): stdClass
    {
        $valueMap = (object) [];

        $contents = $entity->get($field);

        if (!$contents) {
            return $valueMap;
        }

        $matches = [];

        $matchResult = preg_match_all("/\?entryPoint=attachment&amp;id=([^&=\"']+)/", $contents, $matches);

        if (
            !$matchResult ||
            empty($matches[1]) ||
            !is_array($matches[1])
        ) {
            return $valueMap;
        }

        $attachmentIdList = $matches[1];

        /** @var Attachment[] $attachmentList */
        $attachmentList = [];

        foreach ($attachmentIdList as $id) {
            $attachment = $this->entityManager->getRDBRepositoryByClass(Attachment::class)->getById($id);

            if (!$attachment) {
                continue;
            }

            if (!$this->acl->checkEntityRead($attachment)) {
                continue;
            }

            if (!in_array($attachment->getType(), $this->getAllowedImageFileTypeList())) {
                continue;
            }

            $attachmentList[] = $attachment;
        }

        if (!count($attachmentList)) {
            return $valueMap;
        }

        /** @var AttachmentRepository $attachmentRepository */
        $attachmentRepository = $this->entityManager->getRepository(Attachment::ENTITY_TYPE);

        foreach ($attachmentList as $attachment) {
            $copiedAttachment = $attachmentRepository->getCopiedAttachment($attachment);

            $copiedAttachment->setTargetField($field);

            $copiedAttachment->setMultiple([
                'relatedId' => null,
                'relatedType' => $entity->getEntityType(),
            ]);

            $this->entityManager->saveEntity($copiedAttachment);

            $contents = str_replace(
                '?entryPoint=attachment&amp;id=' . $attachment->getId(),
                '?entryPoint=attachment&amp;id=' . $copiedAttachment->getId(),
                $contents
            );
        }

        $valueMap->$field = $contents;

        return $valueMap;
    }

    /**
     * @return string[]
     */
    private function getAllowedImageFileTypeList(): array
    {
        return $this->metadata->get(['app', 'image', 'allowedFileTypeList']) ?? [];
    }
}
