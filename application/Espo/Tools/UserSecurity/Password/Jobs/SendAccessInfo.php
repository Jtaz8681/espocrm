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

use Espo\Core\Mail\Exceptions\SendingError;
use Espo\Entities\User;
use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data;
use Espo\Core\Exceptions\Error;
use Espo\ORM\EntityManager;
use Espo\Tools\UserSecurity\Password\Service as PasswordService;

class SendAccessInfo implements Job
{
    private EntityManager $entityManager;
    private PasswordService $passwordService;

    public function __construct(EntityManager $entityManager, PasswordService $passwordService)
    {
        $this->entityManager = $entityManager;
        $this->passwordService = $passwordService;
    }

    /**
     * @throws SendingError
     * @throws Error
     */
    public function run(Data $data): void
    {
        $userId = $data->getTargetId();

        if (!$userId) {
            throw new Error();
        }

        /** @var ?User $user */
        $user = $this->entityManager->getEntityById(User::ENTITY_TYPE, $userId);

        if (!$user) {
            throw new Error("User '{$userId}' not found.");
        }

        $this->passwordService->sendAccessInfoForNewUser($user);
    }
}
