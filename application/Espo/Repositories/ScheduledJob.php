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

use Espo\Entities\Job as JobEntity;
use Espo\ORM\Entity;
use Espo\Core\Job\Job\Status;
use Espo\Core\Repositories\Database;

/**
 * @extends Database<\Espo\Entities\ScheduledJob>
 */
class ScheduledJob extends Database
{
    protected function afterSave(Entity $entity, array $options = [])
    {
        parent::afterSave($entity, $options);

        if ($entity->isAttributeChanged('scheduling')) {
            $jobList = $this->entityManager
                ->getRDBRepository(JobEntity::ENTITY_TYPE)
                ->where([
                    'scheduledJobId' => $entity->getId(),
                    'status' => Status::PENDING,
                ])
                ->find();

            foreach ($jobList as $job) {
                $this->entityManager->removeEntity($job);
            }
        }
    }
}
