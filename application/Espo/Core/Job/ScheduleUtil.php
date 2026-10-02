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

namespace Espo\Core\Job;

use Espo\Core\Utils\DateTime as DateTimeUtil;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\ORM\Collection;
use Espo\ORM\EntityManager;
use Espo\Entities\ScheduledJob as ScheduledJobEntity;
use Espo\Entities\ScheduledJobLogRecord as ScheduledJobLogRecordEntity;
use Espo\ORM\Name\Attribute;

class ScheduleUtil
{
    public function __construct(private EntityManager $entityManager)
    {}

    /**
     * Get active scheduled job list.
     *
     * @return Collection<ScheduledJobEntity>
     */
    public function getActiveScheduledJobList(): Collection
    {
        /** @var Collection<ScheduledJobEntity> $collection */
        $collection = $this->entityManager
            ->getRDBRepository(ScheduledJobEntity::ENTITY_TYPE)
            ->select([
                Attribute::ID,
                'scheduling',
                'job',
                'name',
            ])
            ->where([
                'status' => ScheduledJobEntity::STATUS_ACTIVE,
            ])
            ->find();

        return $collection;
    }

    /**
     * Add record to ScheduledJobLogRecord about executed job.
     */
    public function addLogRecord(
        string $scheduledJobId,
        string $status,
        ?string $runTime = null,
        ?string $targetId = null,
        ?string $targetType = null
    ): void {

        if (!isset($runTime)) {
            $runTime = date(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);
        }

        /** @var ScheduledJobEntity|null $scheduledJob */
        $scheduledJob = $this->entityManager->getEntityById(ScheduledJobEntity::ENTITY_TYPE, $scheduledJobId);

        if (!$scheduledJob) {
            return;
        }

        $scheduledJob->set('lastRun', $runTime);

        $this->entityManager->saveEntity($scheduledJob, [SaveOption::SILENT => true]);

        $scheduledJobLog = $this->entityManager->getNewEntity(ScheduledJobLogRecordEntity::ENTITY_TYPE);

        $scheduledJobLog->set([
            'scheduledJobId' => $scheduledJobId,
            'name' => $scheduledJob->getName(),
            'status' => $status,
            'executionTime' => $runTime,
            'targetId' => $targetId,
            'targetType' => $targetType,
        ]);

        $this->entityManager->saveEntity($scheduledJobLog);
    }
}
