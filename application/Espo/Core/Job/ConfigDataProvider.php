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

class ConfigDataProvider
{
    private const MAX_PORTION = 15;

    public function __construct(
        private Config $config,
        private Config\ApplicationConfig $applicationConfig,
    ) {}

    public function runInParallel(): bool
    {
        return (bool) $this->config->get('jobRunInParallel');
    }

    public function getMaxPortion(): int
    {
        return (int) $this->config->get('jobMaxPortion', self::MAX_PORTION);
    }

    public function getCronMinInterval(): int
    {
        return (int) $this->config->get('cronMinInterval', 0);
    }

    public function noTableLocking(): bool
    {
        return (bool) $this->config->get('jobNoTableLocking');
    }

    public function getTimeZone(): string
    {
        if ($this->config->get('jobForceUtc')) {
            return 'UTC';
        }

        return $this->applicationConfig->getTimeZone();
    }
}
