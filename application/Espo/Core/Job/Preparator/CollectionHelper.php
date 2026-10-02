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

namespace Espo\Core\Job\Preparator;

use Espo\Core\Job\Job\Status;
use Espo\Core\Utils\DateTime;
use Espo\Entities\Job;
use Espo\ORM\Collection;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

use DateTimeImmutable;
use Espo\ORM\Name\Attribute;

/**
 * Creates jobs for each entity of a collection.
 * To be used by Preparator implementations.
 *
 * @template TEntity of Entity
 */
class CollectionHelper
{
    public function __construct(private EntityManager $entityManager)
    {}

    /**
     * @param Collection<TEntity> $collection
     */
    public function prepare(Collection $collection, Data $data, DateTimeImmutable $executeTime): void
    {
        foreach ($collection as $entity) {
            $this->prepareItem($entity, $data, $executeTime);
        }
    }

    /**
     * @param TEntity $entity
     */
    private function prepareItem(Entity $entity, Data $data, DateTimeImmutable $executeTime): void
    {
        $running = $this->entityManager
            ->getRDBRepository(Job::ENTITY_TYPE)
            ->select(Attribute::ID)
            ->where([
                'scheduledJobId' => $data->getId(),
                'status' => [
                    Status::RUNNING,
                    Status::READY,
                ],
                'targetType' => $entity->getEntityType(),
                'targetId' => $entity->getId(),
            ])
            ->findOne();

        if ($running) {
            return;
        }

        $countPending = $this->entityManager
            ->getRDBRepository(Job::ENTITY_TYPE)
            ->where([
                'scheduledJobId' => $data->getId(),
                'status' => Status::PENDING,
                'targetType' => $entity->getEntityType(),
                'targetId' => $entity->getId(),
            ])
            ->count();

        if ($countPending > 1) {
            return;
        }

        $job = $this->entityManager->getNewEntity(Job::ENTITY_TYPE);

        $job->set([
            'name' => $data->getName(),
            'scheduledJobId' => $data->getId(),
            'executeTime' => $executeTime->format(DateTime::SYSTEM_DATE_TIME_FORMAT),
            'targetType' => $entity->getEntityType(),
            'targetId' => $entity->getId(),
        ]);

        $this->entityManager->saveEntity($job);
    }
}
