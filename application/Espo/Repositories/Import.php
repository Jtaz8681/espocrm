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

namespace Espo\Repositories;

use Espo\Entities\Import as ImportEntity;
use Espo\Entities\ImportEntity as ImportEntityEntity;
use Espo\ORM\Collection;
use Espo\ORM\Entity;
use Espo\ORM\Query\Select as Query;
use Espo\ORM\Query\SelectBuilder;
use Espo\Entities\Attachment as AttachmentEntity;
use Espo\Core\Repositories\Database;
use Espo\Entities\ImportError;

use LogicException;

/**
 * @extends Database<ImportEntity>
 */
class Import extends Database
{
    /**
     * @return Collection<Entity>
     */
    public function findResultRecords(ImportEntity $entity, string $relationName, Query $query): Collection
    {
        $entityType = $entity->getTargetEntityType();

        if (!$entityType) {
            throw new LogicException();
        }

        $modifiedQuery = $this->addImportEntityJoin($entity, $relationName, $query);

        return $this->entityManager
            ->getRDBRepository($entityType)
            ->clone($modifiedQuery)
            ->find();
    }

    protected function addImportEntityJoin(ImportEntity $entity, string $link, Query $query): Query
    {
        $entityType = $entity->getTargetEntityType();

        if (!$entityType) {
            throw new LogicException();
        }

        switch ($link) {
            case 'imported':
                $param = 'isImported';

                break;

            case 'duplicates':
                $param = 'isDuplicate';

                break;

            case 'updated':
                $param = 'isUpdated';

                break;

            default:
                return $query;
        }

        $builder = SelectBuilder::create()->clone($query);

        $builder->join(
            'ImportEntity',
            'importEntity',
            [
                'importEntity.importId' => $entity->getId(),
                'importEntity.entityType' => $entityType,
                'importEntity.entityId:' => 'id',
                'importEntity.' . $param => true,
            ]
        );

        return $builder->build();
    }

    public function countResultRecords(ImportEntity $entity, string $relationName, ?Query $query = null): int
    {
        $entityType = $entity->getTargetEntityType();

        if (!$entityType) {
            throw new LogicException();
        }

        $query = $query ??
            $this->entityManager
            ->getQueryBuilder()
            ->select()
            ->from($entityType)
            ->build();

        $modifiedQuery = $this->addImportEntityJoin($entity, $relationName, $query);

        return $this->entityManager
            ->getRDBRepository($entityType)
            ->clone($modifiedQuery)
            ->count();
    }

    /**
     * @param ImportEntity $entity
     */
    protected function afterRemove(Entity $entity, array $options = [])
    {
        $fileId = $entity->getFileId();

        if ($fileId) {
            $attachment = $this->entityManager->getEntityById(AttachmentEntity::ENTITY_TYPE, $fileId);

            if ($attachment) {
                $this->entityManager->removeEntity($attachment);
            }
        }

        $delete1 = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from(ImportEntityEntity::ENTITY_TYPE)
            ->where([
                'importId' => $entity->getId(),
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($delete1);

        $delete2 = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from(ImportError::ENTITY_TYPE)
            ->where([
                'importId' => $entity->getId(),
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($delete2);

        parent::afterRemove($entity, $options);
    }
}
