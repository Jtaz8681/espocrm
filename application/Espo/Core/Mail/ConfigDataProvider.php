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

namespace Espo\Core\Mail;

use Espo\Core\Utils\Config;

class ConfigDataProvider
{
    public function __construct(
        private Config $config,
    ) {}

    public function getSystemOutboundAddress(): ?string
    {
        return $this->config->get('outboundEmailFromAddress');
    }

    public function isSystemOutboundAddressShared(): bool
    {
        return (bool) $this->config->get('outboundEmailIsShared');
    }
}
