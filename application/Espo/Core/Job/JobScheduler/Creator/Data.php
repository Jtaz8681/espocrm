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

namespace Espo\Core\Job\JobScheduler\Creator;

use Espo\Core\Field\DateTime;
use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data as JobData;
use Espo\Core\Job\JobDataLess;

readonly class Data
{
    /**
     * @param class-string<Job|JobDataLess> $className
     */
    public function __construct(
        public string $className,
        public ?string $queue,
        public ?string $group,
        public JobData $data,
        public ?DateTime $time,
    ) {}
}
