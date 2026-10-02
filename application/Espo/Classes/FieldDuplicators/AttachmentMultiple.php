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

use Espo\Core\Record\Duplicator\FieldDuplicator;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

use Espo\Repositories\Attachment as AttachmentRepository;
use Espo\Entities\Attachment;

use stdClass;

class AttachmentMultiple implements FieldDuplicator
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function duplicate(Entity $entity, string $field): stdClass
    {
        $valueMap = (object) [];

        /** @var \Espo\ORM\Collection<Attachment> $attachmentList */
        $attachmentList = $this->entityManager
            ->getRDBRepository($entity->getEntityType())
            ->getRelation($entity, $field)
            ->find();

        if (is_countable($attachmentList) && !count($attachmentList)) {
            return $valueMap;
        }

        $idList = [];
        $nameHash = (object) [];
        $typeHash = (object) [];

        /** @var AttachmentRepository $attachmentRepository */
        $attachmentRepository = $this->entityManager->getRepository(Attachment::ENTITY_TYPE);

        foreach ($attachmentList as $attachment) {
            $copiedAttachment = $attachmentRepository->getCopiedAttachment($attachment);

            $copiedAttachment->set('field', $field);

            $this->entityManager->saveEntity($copiedAttachment);

            $idList[] = $copiedAttachment->getId();

            $nameHash->{$copiedAttachment->getId()} = $copiedAttachment->getName();
            $typeHash->{$copiedAttachment->getId()} = $copiedAttachment->getType();
        }

        $valueMap->{$field . 'Ids'} = $idList;
        $valueMap->{$field . 'Names'} = $nameHash;
        $valueMap->{$field . 'Types'} = $typeHash;

        return $valueMap;
    }
}
