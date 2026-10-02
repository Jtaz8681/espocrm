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

namespace Espo\Core\Utils\Config;

use Espo\Core\Utils\Config;

/**
 * @since 9.0.0
 */
class ApplicationConfig
{
    public function __construct(
        private Config $config,
    ) {}

    public function getSiteUrl(): string
    {
        return rtrim($this->config->get('siteUrl') ?? '', '/');
    }

    public function getDateFormat(): string
    {
        return $this->config->get('dateFormat') ?? 'DD.MM.YYYY';
    }

    public function getTimeFormat(): string
    {
        return $this->config->get('timeFormat') ?? 'HH:mm';
    }

    public function getTimeZone(): string
    {
        return $this->config->get('timeZone') ?? 'UTC';
    }

    public function getLanguage(): string
    {
        return $this->config->get('language') ?? 'en_US';
    }

    /**
     * @since 9.2.0
     */
    public function getRecordsPerPage(): int
    {
        return (int) $this->config->get('recordsPerPage');
    }
}
