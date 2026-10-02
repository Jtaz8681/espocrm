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

namespace Espo\Tools\UserSecurity\Password\Jobs;

use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data;
use Espo\Entities\PasswordChangeRequest;
use Espo\ORM\EntityManager;
use RuntimeException;

class RemoveRecoveryRequest implements Job
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function run(Data $data): void
    {
        $id = $data->get('id');

        if (!$id) {
            throw new RuntimeException();
        }

        $entity = $this->entityManager->getEntityById(PasswordChangeRequest::ENTITY_TYPE, $id);

        if (!$entity) {
            return;
        }

        $this->entityManager->removeEntity($entity);
    }
}
