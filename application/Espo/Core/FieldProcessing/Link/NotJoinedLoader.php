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

namespace Espo\Core\FieldProcessing\Link;

use Espo\Core\Name\Field;
use Espo\ORM\Entity;

use Espo\Core\FieldProcessing\Loader as LoaderInterface;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;
use Espo\ORM\Defs as OrmDefs;
use Espo\ORM\Name\Attribute;

/**
 * @implements LoaderInterface<Entity>
 */
class NotJoinedLoader implements LoaderInterface
{
    /** @var array<string, string[]> */
    private array $fieldListCacheMap = [];

    public function __construct(
        private OrmDefs $ormDefs,
        private EntityManager $entityManager
    ) {}

    public function process(Entity $entity, Params $params): void
    {
        foreach ($this->getFieldList($entity->getEntityType()) as $field) {
            $this->processItem($entity, $field);
        }
    }

    private function processItem(Entity $entity, string $field): void
    {
        $nameAttribute = $field . 'Name';
        $idAttribute = $field . 'Id';

        $id = $entity->get($idAttribute);

        if (!$id) {
            $entity->set($nameAttribute, null);

            return;
        }

        if ($entity->get($nameAttribute)) {
            return;
        }

        $foreignEntityType = $this->ormDefs
            ->getEntity($entity->getEntityType())
            ->getRelation($field)
            ->getForeignEntityType();

        $foreignEntity = $this->entityManager
            ->getRDBRepository($foreignEntityType)
            ->select([Attribute::ID, Field::NAME])
            ->where([Attribute::ID => $id])
            ->findOne();

        if (!$foreignEntity) {
            /** @noinspection PhpRedundantOptionalArgumentInspection */
            $entity->set($nameAttribute, null);

            return;
        }

        $name = $foreignEntity->get(Field::NAME);

        if ($name === null) {
            $foreignEntity = $this->entityManager
                ->getRDBRepository($foreignEntityType)
                ->getById($id);

            if ($foreignEntity) {
                $name = $foreignEntity->get(Field::NAME);
            }
        }

        $entity->set($nameAttribute, $name);
    }

    /**
     * @return string[]
     */
    private function getFieldList(string $entityType): array
    {
        if (array_key_exists($entityType, $this->fieldListCacheMap)) {
            return $this->fieldListCacheMap[$entityType];
        }

        $list = [];

        $entityDefs = $this->ormDefs->getEntity($entityType);

        foreach ($entityDefs->getRelationList() as $relationDefs) {
            if ($relationDefs->getType() !== Entity::BELONGS_TO) {
                continue;
            }

            // Commented to load name of leads w/o person name.
            /*if (!$relationDefs->getParam('noJoin')) {
                continue;
            }*/

            if (!$relationDefs->hasForeignEntityType()) {
                continue;
            }

            $foreignEntityType = $relationDefs->getForeignEntityType();

            if (!$this->entityManager->hasRepository($foreignEntityType)) {
                continue;
            }

            $name = $relationDefs->getName();

            if (!$entityDefs->hasAttribute($name . 'Id')) {
                continue;
            }

            if (!$entityDefs->hasAttribute($name . 'Name')) {
                continue;
            }

            $list[] = $name;
        }

        $this->fieldListCacheMap[$entityType] = $list;

        return $list;
    }
}
