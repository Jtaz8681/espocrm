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

namespace Espo\Core\Record\Deleted;

use Espo\Core\Exceptions\Conflict;
use Espo\Core\Field\DateTime;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Defs\RelationDefs;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Repository\Util;

/**
 * @implements Restorer<Entity>
 */
class DefaultRestorer implements Restorer
{
    public function __construct(
        private EntityManager $entityManager,
        private Metadata $metadata,
        private RestorerFactory $restorerFactory,
    ) {}

    public function restore(Entity $entity): void
    {
        if (!$entity->get(Attribute::DELETED)) {
            throw new Conflict("Entity is not soft-deleted.");
        }

        $this->entityManager
            ->getTransactionManager()
            ->run(fn () => $this->restoreInTransaction($entity));
    }

    /**
     * @throws Conflict
     */
    private function restoreInTransaction(Entity $entity): void
    {
        $modifiedAt = $this->getModifiedAt($entity);

        $repository = $this->entityManager->getRDBRepository($entity->getEntityType());

        $repository->restoreDeleted($entity->getId());

        if (
            $entity->hasAttribute('deleteId') &&
            $this->metadata->get("entityDefs.{$entity->getEntityType()}.deleteId")
        ) {
            $this->entityManager->refreshEntity($entity);

            $entity->set('deleteId', '0');
            $repository->save($entity, [SaveOption::SILENT => true]);
        }

        if ($modifiedAt) {
            $this->restoreRelatedRecords($entity, $modifiedAt);
        }
    }

    /**
     * @throws Conflict
     */
    private function restoreRelatedRecords(Entity $entity, DateTime $modifiedAt): void
    {
        $relations = $this->entityManager
            ->getDefs()
            ->getEntity($entity->getEntityType())
            ->getRelationList();

        foreach ($relations as $relation) {
            if (!$relation->getParam(RelationParam::CASCADE_REMOVAL)) {
                continue;
            }

            $this->restoreRelatedLink($entity, $relation, $modifiedAt);
        }
    }

    /**
     * @throws Conflict
     */
    private function restoreRelatedLink(Entity $entity, RelationDefs $relation, DateTime $modifiedAt): void
    {
        $foreignEntityType = $relation->tryGetForeignEntityType();
        $foreign = $relation->tryGetForeignRelationName();

        if (!$foreignEntityType || !$foreign) {
            return;
        }

        $foreignType = $this->entityManager
            ->getDefs()
            ->tryGetEntity($foreignEntityType)
            ?->tryGetRelation($foreign)
            ?->getType();

        if (!$foreignType) {
            return;
        }

        if (!Util::isRelationshipEligibleForCascadeRemoval($relation->getType(), $foreignType)) {
            return;
        }

        $link = $relation->getName();

        $builder = SelectBuilder::create()
            ->from($foreignEntityType)
            ->withDeleted();

        $collection = $this->entityManager
            ->getRelation($entity, $link)
            ->clone($builder->build())
            ->sth()
            ->where([
                Attribute::DELETED => true,
                Field::MODIFIED_AT . '>=' => $modifiedAt->toString(),
            ])
            ->find();

        foreach ($collection as $relatedEntity) {
            $this->restoreRelated($relatedEntity);
        }
    }

    /**
     * @throws Conflict
     */
    private function restoreRelated(Entity $relatedEntity): void
    {
        if (
            !$relatedEntity->hasAttribute(Attribute::DELETED) ||
            !$relatedEntity->get(Attribute::DELETED)
        ) {
            return;
        }

        $restorer = $this->restorerFactory->create($relatedEntity->getEntityType());

        $restorer->restore($relatedEntity);
    }


    private function getModifiedAt(Entity $entity): ?DateTime
    {
        $modifiedAtString = $entity->get(Field::MODIFIED_AT);

        return $modifiedAtString ? DateTime::fromString($modifiedAtString) : null;
    }
}
