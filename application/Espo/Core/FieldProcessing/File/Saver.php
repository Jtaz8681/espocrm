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

namespace Espo\Core\FieldProcessing\File;

use Espo\Entities\Attachment;
use Espo\ORM\Entity;

use Espo\Core\FieldProcessing\Saver as SaverInterface;
use Espo\Core\FieldProcessing\Saver\Params;
use Espo\Core\ORM\EntityManager;

/**
 * @implements SaverInterface<Entity>
 */
class Saver implements SaverInterface
{
    private EntityManager $entityManager;

    /** @var array<string, string[]>*/
    private $fieldListMapCache = [];

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function process(Entity $entity, Params $params): void
    {
        foreach ($this->getFieldList($entity->getEntityType()) as $name) {
            $this->processItem($entity, $name);
        }
    }

    private function processItem(Entity $entity, string $name): void
    {
        $attribute = $name . 'Id';

        if (!$entity->isAttributeChanged($attribute)) {
            return;
        }

        $id = $entity->get($attribute);

        if (!$id) {
            $this->removePrevious($entity, $attribute);

            return;
        }

        $attachment = $this->entityManager->getEntityById(Attachment::ENTITY_TYPE, $id);

        if (!$attachment) {
            return;
        }

        $attachment->set([
            'relatedId' => $entity->getId(),
            'relatedType' => $entity->getEntityType(),
        ]);

        $this->entityManager->saveEntity($attachment);

        if ($entity->isNew()) {
            return;
        }

        $this->removePrevious($entity, $attribute);
    }

    private function removePrevious(Entity $entity, string $attribute): void
    {
        $previousId = $entity->getFetched($attribute);

        if (!$previousId) {
            return;
        }

        $previousAttachment = $this->entityManager->getEntityById(Attachment::ENTITY_TYPE, $previousId);

        if (!$previousAttachment) {
            return;
        }

        $this->entityManager->removeEntity($previousAttachment);
    }

    /**
     * @return string[]
     */
    private function getFieldList(string $entityType): array
    {
        if (array_key_exists($entityType, $this->fieldListMapCache)) {
            return $this->fieldListMapCache[$entityType];
        }

        $entityDefs = $this->entityManager
            ->getDefs()
            ->getEntity($entityType);

        $list = [];

        foreach ($entityDefs->getRelationNameList() as $name) {
            $defs = $entityDefs->getRelation($name);

            $type = $defs->getType();

            if (!$defs->hasForeignEntityType()) {
                continue;
            }

            $foreignEntityType = $defs->getForeignEntityType();

            if ($type !== Entity::BELONGS_TO || $foreignEntityType !== 'Attachment') {
                continue;
            }


            if (!$entityDefs->hasAttribute($name . 'Id')) {
                continue;
            }

            $list[] = $name;
        }

        $this->fieldListMapCache[$entityType] = $list;

        return $list;
    }
}
