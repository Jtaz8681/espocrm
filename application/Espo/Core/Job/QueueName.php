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

class QueueName
{
    /**
     * Executes as soon as possible. Non-parallel.
     */
    public const Q0 = 'q0';

    /**
     * Executes every minute. Non-parallel.
     */
    public const Q1 = 'q1';

    /**
     * Executes as soon as possible. For email processing. Non-parallel.
     */
    public const E0 = 'e0';

    /**
     * Executes in the main queue pool in parallel. Along with jobs without specified queue.
     * A portion is always picked for a queue iteration, even if there are no-queue
     * jobs ordered before. E.g. if the portion size is 100, and there are 200 empty-queue
     * jobs and 5 m0 jobs, 95 and 5 will be picked respectfully.
     *
     * @since 9.2.0
     */
    const M0 = 'm0';
}
