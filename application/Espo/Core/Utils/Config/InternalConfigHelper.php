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
use Espo\Core\Utils\Metadata;

class InternalConfigHelper
{
    /** @var string[]  */
    private array $stateParamList = [
        'appTimestamp',
        'cacheTimestamp',
        'version',
        'latestVersion',
        'latestExtensionVersions',
        'currencyRates',
    ];

    public function __construct(private Config $config, private Metadata $metadata)
    {}

    public function isParamForStateConfig(string $name): bool
    {
        return in_array($name, $this->stateParamList);
    }

    public function isParamForInternalConfig(string $name): bool
    {
        if ($this->config->isInternal($name)) {
            return true;
        }

        if (in_array($name, $this->config->get('systemItems') ?? [])) {
            return true;
        }

        $level = $this->metadata->get(['app', 'config', 'params', $name, 'level']);

        if ($level === Access::LEVEL_SYSTEM || $level === Access::LEVEL_INTERNAL) {
            return true;
        }

        return false;
    }
}
