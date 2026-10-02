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

namespace Espo\Core\FieldProcessing\Relation;

use Espo\Core\ORM\Defs\AttributeParam;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\ORM\Entity;

use Espo\Core\FieldProcessing\Saver as SaverInterface;
use Espo\Core\FieldProcessing\Saver\Params;
use Espo\Core\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Repository\Option\SaveOption;

/**
 * @implements SaverInterface<Entity>
 */
class Saver implements SaverInterface
{
    private EntityManager $entityManager;
    private LinkMultipleSaver $linkMultipleSaver;

    /** @var array<string, string[]> */
    private $manyRelationListMapCache = [];
    /** @var array<string, string[]> */
    private $hasOneRelationListMapCache = [];
    /** @var array<string, string[]> */
    private $belongsToHasOneRelationListMapCache = [];

    public function __construct(
        EntityManager $entityManager,
        LinkMultipleSaver $linkMultipleSaver
    ) {
        $this->entityManager = $entityManager;
        $this->linkMultipleSaver = $linkMultipleSaver;
    }

    public function process(Entity $entity, Params $params): void
    {
        $this->processMany($entity, $params);
        $this->processHasOne($entity);
        $this->processBelongsToHasOne($entity);
    }

    private function processMany(Entity $entity, Params $params): void
    {
        $entityType = $entity->getEntityType();

        foreach ($this->getManyRelationList($entityType) as $name) {
            $this->processManyItem($entity, $name, $params);
        }
    }

    private function processManyItem(Entity $entity, string $name, Params $params): void
    {
        $idsAttribute = $name . 'Ids';
        $columnsAttribute = $name . 'Columns';

        if (!$entity->has($idsAttribute) && !$entity->has($columnsAttribute)) {
            return;
        }

        if (!$entity instanceof CoreEntity) {
            return;
        }

        $this->linkMultipleSaver->process($entity, $name, $params);
    }

    private function processHasOne(Entity $entity): void
    {
        $entityType = $entity->getEntityType();

        foreach ($this->getHasOneRelationList($entityType) as $name) {
            $this->processHasOneItem($entity, $name);
        }
    }

    private function processHasOneItem(Entity $entity, string $name): void
    {
        $entityType = $entity->getEntityType();
        $idAttribute = $name . 'Id';

        if (!$entity->has($idAttribute) || !$entity->isAttributeChanged($idAttribute)) {
            return;
        }

        /** @var ?string $id */
        $id = $entity->get($idAttribute);

        $defs = $this->entityManager->getDefs()->getEntity($entityType);
        $relationDefs = $defs->getRelation($name);

        $foreignKey = $relationDefs->getForeignKey();
        $foreignEntityType = $relationDefs->getForeignEntityType();

        $previous = $this->entityManager
            ->getRDBRepository($foreignEntityType)
            ->select([Attribute::ID])
            ->where([$foreignKey => $entity->getId()])
            ->findOne();

        if (!$entity->isNew() && !$entity->hasFetched($idAttribute)) {
            $entity->setFetched($idAttribute, $previous ? $previous->getId() : null);
        }

        if ($previous) {
            if (!$id) {
                $this->entityManager
                    ->getRelation($entity, $name)
                    ->unrelate($previous);

                return;
            }

            if ($previous->getId() === $id) {
                return;
            }
        }

        if (!$id) {
            return;
        }

        $this->entityManager
            ->getRelation($entity, $name)
            ->relateById($id);
    }

    private function processBelongsToHasOne(Entity $entity): void
    {
        $entityType = $entity->getEntityType();

        foreach ($this->getBelongsToHasOneRelationList($entityType) as $name) {
            $this->processBelongsToHasOneItem($entity, $name);
        }
    }

    private function processBelongsToHasOneItem(Entity $entity, string $name): void
    {
        $entityType = $entity->getEntityType();

        $idAttribute = $name . 'Id';

        if (!$entity->get($idAttribute)) {
            return;
        }

        if (!$entity->isAttributeChanged($idAttribute)) {
            return;
        }

        $anotherEntity = $this->entityManager
            ->getRDBRepository($entityType)
            ->select([Attribute::ID])
            ->where([
                $idAttribute => $entity->get($idAttribute),
                Attribute::ID . '!=' => $entity->getId(),
            ])
            ->findOne();

        if (!$anotherEntity) {
            return;
        }

        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $anotherEntity->set($idAttribute, null);

        $this->entityManager->saveEntity($anotherEntity, [
            SaveOption::SKIP_ALL => true,
        ]);
    }

    /**
     * @return string[]
     */
    private function getManyRelationList(string $entityType): array
    {
        if (array_key_exists($entityType, $this->manyRelationListMapCache)) {
            return $this->manyRelationListMapCache[$entityType];
        }

        $typeList = [
            Entity::HAS_MANY,
            Entity::MANY_MANY,
            Entity::HAS_CHILDREN,
        ];

        $defs = $this->entityManager->getDefs()->getEntity($entityType);

        $list = [];

        foreach ($defs->getRelationNameList() as $name) {
            $type = $defs->getRelation($name)->getType();

            if (!in_array($type, $typeList)) {
                continue;
            }

            $idsAttribute = $name . 'Ids';

            if (!$defs->hasAttribute($idsAttribute)) {
                continue;
            }

            $attributeDefs = $defs->getAttribute($idsAttribute);

            if (
                !$attributeDefs->getParam(AttributeParam::IS_LINK_MULTIPLE_ID_LIST) &&
                !$attributeDefs->getParam(AttributeParam::IS_LINK_STUB)
            ) {
                continue;
            }

            if ($defs->hasField($name) && $defs->getField($name)->getParam('noSave')) {
                continue;
            }

            $list[] = $name;
        }

        $this->manyRelationListMapCache[$entityType] = $list;

        return $list;
    }

    /**
     * @return string[]
     */
    private function getHasOneRelationList(string $entityType): array
    {
        if (array_key_exists($entityType, $this->hasOneRelationListMapCache)) {
            return $this->hasOneRelationListMapCache[$entityType];
        }

        $ormDefs = $this->entityManager->getDefs();

        $defs = $ormDefs->getEntity($entityType);

        $list = [];

        foreach ($defs->getRelationNameList() as $name) {
            $relationDefs = $defs->getRelation($name);

            $type = $relationDefs->getType();

            if ($type !== Entity::HAS_ONE) {
                continue;
            }

            if (!$relationDefs->hasForeignEntityType()) {
                continue;
            }

            if (!$relationDefs->hasForeignKey()) {
                continue;
            }

            if (!$defs->hasAttribute($name . 'Id')) {
                continue;
            }

            if ($defs->hasField($name) && $defs->getField($name)->getParam('noSave')) {
                continue;
            }

            $list[] = $name;
        }

        $this->hasOneRelationListMapCache[$entityType] = $list;

        return $list;
    }

    /**
     * @return string[]
     */
    private function getBelongsToHasOneRelationList(string $entityType): array
    {
        if (array_key_exists($entityType, $this->belongsToHasOneRelationListMapCache)) {
            return $this->belongsToHasOneRelationListMapCache[$entityType];
        }

        $ormDefs = $this->entityManager->getDefs();

        $defs = $ormDefs->getEntity($entityType);

        $list = [];

        foreach ($defs->getRelationNameList() as $name) {
            $relationDefs = $defs->getRelation($name);

            $type = $relationDefs->getType();

            if ($type !== Entity::BELONGS_TO) {
                continue;
            }

            if (!$relationDefs->hasForeignRelationName()) {
                continue;
            }

            if (!$relationDefs->hasForeignEntityType()) {
                continue;
            }

            if (!$defs->hasAttribute($name . 'Id')) {
                continue;
            }

            $foreignEntityType = $relationDefs->getForeignEntityType();
            $foreignRelationName = $relationDefs->getForeignRelationName();

            $foreignType = $ormDefs
                ->getEntity($foreignEntityType)
                ->tryGetRelation($foreignRelationName)
                ?->getType();

            if ($foreignType !== Entity::HAS_ONE) {
                continue;
            }

            $list[] = $name;
        }

        $this->belongsToHasOneRelationListMapCache[$entityType] = $list;

        return $list;
    }
}
