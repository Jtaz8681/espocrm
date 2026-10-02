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

namespace Espo\Modules\Crm\Tools\Calendar\FreeBusy;

use Espo\Core\Field\DateTime;
use Espo\Modules\Crm\Tools\Calendar\Items\Event;

readonly class FetchParams
{
    /**
     * @param bool $accessCheck To user apply access check.
     * @param Event[] $ignoreEventList Events not to be included in a result.
     */
    public function __construct(
        public DateTime $from,
        public DateTime $to,
        public bool $accessCheck = false,
        public array $ignoreEventList = [],
    ) {}
}
