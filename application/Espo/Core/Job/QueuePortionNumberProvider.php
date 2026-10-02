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

use Espo\Core\Utils\Config;

class QueuePortionNumberProvider
{
    /** @var array<string, int> */
    private $queueNumberMap = [
        QueueName::Q0 => self::Q0_PORTION_NUMBER,
        QueueName::Q1 => self::Q1_PORTION_NUMBER,
        QueueName::E0 => self::E0_PORTION_NUMBER,
    ];

    /** @var array<string, string> */
    private $queueParamNameMap = [
        QueueName::Q0 => 'jobQ0MaxPortion',
        QueueName::Q1 => 'jobQ1MaxPortion',
        QueueName::E0 => 'jobE0MaxPortion',
    ];

    private const Q0_PORTION_NUMBER = 200;
    private const Q1_PORTION_NUMBER = 500;
    private const E0_PORTION_NUMBER = 100;
    private const DEFAULT_PORTION_NUMBER = 200;

    public function __construct(private Config $config)
    {}

    public function get(string $queue): int
    {
        $paramName = $this->queueParamNameMap[$queue] ?? 'job' . ucfirst($queue) . 'MaxPortion';

        return
            $this->config->get($paramName) ??
            $this->queueNumberMap[$queue] ??
            self::DEFAULT_PORTION_NUMBER;
    }
}
