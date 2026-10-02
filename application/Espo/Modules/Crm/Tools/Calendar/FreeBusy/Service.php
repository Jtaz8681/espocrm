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

use Espo\Core\Exceptions\Forbidden;
use Espo\Entities\User;
use Espo\Modules\Crm\Tools\Calendar\FetchParams as CalendarFetchParams;
use Espo\Modules\Crm\Tools\Calendar\Items\BusyRange;
use Espo\Modules\Crm\Tools\Calendar\Service as CalendarService;

/**
 * @since 9.0.0
 */
class Service
{
    public function __construct(
        private CalendarService $service,
    ) {}

    /**
     * Fetch busy-ranges for user. Access is not checked by default.
     *
     * @return BusyRange[]
     * @throws Forbidden
     */
    public function fetchRanges(User $user, FetchParams $params): array
    {
        $fetchParams = CalendarFetchParams::create($params->from, $params->to);

        return $this->service->fetchBusyRanges($user, $params, $fetchParams);
    }
}
