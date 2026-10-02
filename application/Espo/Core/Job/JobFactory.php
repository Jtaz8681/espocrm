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

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\ClassFinder;
use RuntimeException;

class JobFactory
{
    public function __construct(
        private ClassFinder $classFinder,
        private InjectableFactory $injectableFactory,
        private MetadataProvider $metadataProvider,
    ) {}

    /**
     * Create a job by a scheduled job name.
     */
    public function create(string $name): Job|JobDataLess
    {
        $className = $this->getClassName($name);

        if (!$className) {
            throw new RuntimeException("Job '$name' not found.");
        }

        return $this->createByClassName($className);
    }

    /**
     * Create a job by a class name.
     *
     * @param class-string<Job|JobDataLess> $className
     */
    public function createByClassName(string $className): Job|JobDataLess
    {
        return $this->injectableFactory->create($className);
    }

    /**
     * @return ?class-string<Job|JobDataLess>
     */
    private function getClassName(string $name): ?string
    {
        /** @var ?class-string<Job|JobDataLess> $className */
        $className = $this->metadataProvider->getJobClassName($name);

        if ($className) {
            return $className;
        }

        /** @var ?class-string<Job|JobDataLess> */
        return $this->classFinder->find('Jobs', ucfirst($name));
    }
}
