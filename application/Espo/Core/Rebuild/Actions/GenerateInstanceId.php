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

namespace Espo\Core\Rebuild\Actions;

use Espo\Core\Rebuild\RebuildAction;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Util;

/**
 * @noinspection PhpUnused
 */
class GenerateInstanceId implements RebuildAction
{
    public function __construct(
        private Config $config,
        private Config\ConfigWriter $configWriter
    ) {}

    public function process(): void
    {
        if ($this->config->get('instanceId')) {
            return;
        }

        $id = Util::generateUuid4();

        $this->configWriter->set('instanceId', $id);
        $this->configWriter->save();
    }
}
