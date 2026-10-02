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

use Espo\Core\Utils\DateTime;
use Espo\Tools\EmailTemplate\Data;
use Espo\Tools\EmailTemplate\Placeholder;

/**
 * @noinspection PhpUnused
 */
class Now implements Placeholder
{
    public function __construct(
        private DateTime $dateTime
    ) {}

    public function get(Data $data): string
    {
        return $this->dateTime->getNowString();
    }
}
