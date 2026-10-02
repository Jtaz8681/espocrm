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

namespace Espo\Tools\Stream\Jobs;

use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\ORM\EntityManager;
use Espo\Tools\Stream\NoteAcl\Processor;

class ProcessNoteAcl implements Job
{
    public function __construct(
        private Processor $processor,
        private EntityManager $entityManager
    ) {}

    public function run(Data $data): void
    {
        $targetType = $data->getTargetType();
        $targetId = $data->getTargetId();
        $notify = $data->get('notify') === true;

        if (!$targetType || !$targetId) {
            return;
        }

        if (!$this->entityManager->hasRepository($targetType)) {
            return;
        }

        $entity = $this->entityManager->getEntityById($targetType, $targetId);

        if (!$entity instanceof CoreEntity) {
            return;
        }

        $this->processor->process($entity, $notify);
    }
}
