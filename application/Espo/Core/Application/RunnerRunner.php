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

namespace Espo\Core\Application;

use Espo\Core\Utils\Log;
use Espo\Core\ApplicationUser;
use Espo\Core\InjectableFactory;
use Espo\Core\Application\Exceptions\RunnerException;
use Espo\Core\Application\Runner\Params;

use ReflectionClass;

/**
 * Runs a runner.
 */
class RunnerRunner
{
    public function __construct(
        private Log $log,
        private ApplicationUser $applicationUser,
        private InjectableFactory $injectableFactory
    ) {}

    /**
     * @param class-string<Runner|RunnerParameterized> $className
     * @throws RunnerException
     */
    public function run(string $className, ?Params $params = null): void
    {
        if (!class_exists($className)) {
            $this->log->error("Application runner '$className' does not exist.");

            throw new RunnerException();
        }

        $class = new ReflectionClass($className);

        if (
            $class->getStaticPropertyValue('cli', false) &&
            !str_starts_with(php_sapi_name() ?: '', 'cli')
        ) {
            throw new RunnerException("Can be run only via CLI.");
        }

        if ($class->getStaticPropertyValue('setupSystemUser', false)) {
            $this->applicationUser->setupSystemUser();
        }

        $runner = $this->injectableFactory->create($className);

        if ($runner instanceof RunnerParameterized) {
            $runner->run($params ?? Params::create());

            return;
        }

        $runner->run();
    }
}
