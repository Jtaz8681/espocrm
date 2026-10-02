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

namespace Espo\Core\FieldProcessing\MultiEnum;

use Espo\Entities\ArrayValue;
use Espo\ORM\Entity;
use Espo\Core\ORM\Entity as CoreEntity;

use Espo\Repositories\ArrayValue as Repository;

use Espo\Core\FieldProcessing\Saver as SaverInterface;
use Espo\Core\FieldProcessing\Saver\Params;
use Espo\Core\ORM\EntityManager;

/**
 * @implements SaverInterface<Entity>
 */
class Saver implements SaverInterface
{
    private EntityManager $entityManager;
    /** @var array<string, string[]> */
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
        if (!$entity->has($name)) {
            return;
        }

        if (!$entity->isAttributeChanged($name)) {
            return;
        }

        /** @var Repository $repository */
        $repository = $this->entityManager->getRepository(ArrayValue::ENTITY_TYPE);

        assert($entity instanceof CoreEntity);

        $repository->storeEntityAttribute($entity, $name);
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

        foreach ($entityDefs->getAttributeNameList() as $name) {
            $defs = $entityDefs->getAttribute($name);

            if ($defs->getType() !== Entity::JSON_ARRAY) {
                continue;
            }

            if (!$defs->getParam('storeArrayValues')) {
                continue;
            }

            if (
                $entityDefs->hasField($name) &&
                $entityDefs->getField($name)->getParam('doNotStoreArrayValues')
            ) {
                continue;
            }

            if ($defs->isNotStorable()) {
                continue;
            }

            $list[] = $name;
        }

        $this->fieldListMapCache[$entityType] = $list;

        return $list;
    }
}
