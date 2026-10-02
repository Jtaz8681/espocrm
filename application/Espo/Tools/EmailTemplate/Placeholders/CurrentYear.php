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

namespace Espo\Tools\EmailTemplate\Placeholders;

use DateTime;
use DateTimezone;
use Espo\Core\Utils\Config;
use Espo\Tools\EmailTemplate\Data;
use Espo\Tools\EmailTemplate\Placeholder;
use Exception;
use RuntimeException;

/**
 * @noinspection PhpUnused
 */
class CurrentYear implements Placeholder
{
    public function __construct(
        private Config\ApplicationConfig $applicationConfig,
    ) {}

    public function get(Data $data): string
    {
        try {
            $now = new DateTime('now', new DateTimezone($this->applicationConfig->getTimeZone()));
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        return $now->format('Y');
    }
}
