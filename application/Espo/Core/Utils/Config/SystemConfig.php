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
 * @since 9.1.0
 */
class SystemConfig
{
    public function __construct(
        private Config $config,
    ) {}

    public function useCache(): bool
    {
        return (bool) $this->config->get('useCache');
    }

    public function getVersion(): string
    {
        return (string) $this->config->get('version');
    }

    /**
     * Is restricted mode.
     *
     * @since 9.1.8
     */
    public function isRestrictedMode(): bool
    {
        return (bool) $this->config->get('restrictedMode');
    }

    /**
     * @since 10.0.0
     */
    public function isCronEnabled(): bool
    {
        return !$this->config->get('cronDisabled');
    }
}
