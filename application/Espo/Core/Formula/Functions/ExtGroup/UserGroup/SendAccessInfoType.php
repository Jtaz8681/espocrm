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

namespace Espo\Core\Formula\Functions\ExtGroup\UserGroup;

use Espo\Core\Formula\Exceptions\FunctionRuntimeError;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Job\JobSchedulerFactory;
use Espo\Tools\UserSecurity\Password\Jobs\SendAccessInfo as SendAccessInfoJob;
use Espo\Core\Job\Job\Data as JobData;
use Espo\Entities\User;
use Espo\Core\Di;

/**
 * @noinspection PhpUnused
 */
class SendAccessInfoType extends BaseFunction implements
    Di\EntityManagerAware,
    Di\InjectableFactoryAware
{
    use Di\EntityManagerSetter;
    use Di\InjectableFactorySetter;

    public function process(ArgumentList $args)
    {
        if (count($args) < 1) {
            $this->throwTooFewArguments(1);
        }

        $evaluatedArgs = $this->evaluate($args);

        $userId = $evaluatedArgs[0];

        if (!$userId || !is_string($userId)) {
            $this->throwBadArgumentType(1, 'string');
        }

        $user = $this->entityManager->getEntityById(User::ENTITY_TYPE, $userId);

        if (!$user) {
            throw new FunctionRuntimeError("User '$userId' does not exist.");
        }

        $this->createJobScheduledFactory()
            ->create()
            ->setClassName(SendAccessInfoJob::class)
            ->setData(
                JobData::create()
                    ->withTargetId($user->getId())
            )
            ->schedule();
    }

    private function createJobScheduledFactory(): JobSchedulerFactory
    {
        return $this->injectableFactory->create(JobSchedulerFactory::class);
    }
}
