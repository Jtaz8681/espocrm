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

namespace Espo\Modules\Crm\Tools\Calendar\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Field\DateTime;
use Espo\Modules\Crm\Tools\Calendar\Item as CalendarItem;
use Espo\Modules\Crm\Tools\Calendar\Service;

use stdClass;

/**
 * Busy-ranges.
 */
class GetBusyRanges implements Action
{
    public function __construct(private Service $calendarService) {}

    public function process(Request $request): Response
    {
        $from = $request->getQueryParam('from');
        $to = $request->getQueryParam('to');
        $userIdListString = $request->getQueryParam('userIdList');

        if (!$from || !$to || !$userIdListString) {
            throw new BadRequest();
        }

        $userIdList = explode(',', $userIdListString);

        $map = $this->calendarService->fetchBusyRangesForUsers(
            $userIdList,
            DateTime::fromString($from),
            DateTime::fromString($to),
            $request->getQueryParam('entityType'),
            $request->getQueryParam('entityId')
        );

        $result = (object) [];

        foreach ($map as $userId => $itemList) {
            $result->$userId = self::itemListToRaw($itemList);
        }

        return ResponseComposer::json($result);
    }

    /**
     * @param CalendarItem[] $itemList
     * @return stdClass[]
     */
    private static function itemListToRaw(array $itemList): array
    {
        return array_map(fn (CalendarItem $item) => $item->getRaw(), $itemList);
    }
}
