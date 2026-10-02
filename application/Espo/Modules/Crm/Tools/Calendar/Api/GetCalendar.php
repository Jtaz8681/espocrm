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
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Acl;
use Espo\Core\Field\DateTime;
use Espo\Entities\User;
use Espo\Modules\Crm\Tools\Calendar\FetchParams;
use Espo\Modules\Crm\Tools\Calendar\Item as CalendarItem;
use Espo\Modules\Crm\Tools\Calendar\Service;

use stdClass;

/**
 * Calendar events.
 */
class GetCalendar implements Action
{
    private const MAX_CALENDAR_RANGE = 123;

    public function __construct(
        private Service $calendarService,
        private Acl $acl,
        private User $user
    ) {}

    public function process(Request $request): Response
    {
        if (!$this->acl->check('Calendar')) {
            throw new Forbidden();
        }

        $from = $request->getQueryParam('from');
        $to = $request->getQueryParam('to');
        $isAgenda = $request->getQueryParam('agenda') === 'true';

        if (empty($from) || empty($to)) {
            throw new BadRequest();
        }

        if (strtotime($to) - strtotime($from) > self::MAX_CALENDAR_RANGE * 24 * 3600) {
            throw new Forbidden('Too long range.');
        }

        $scopeList = null;

        if ($request->getQueryParam('scopeList') !== null) {
            $scopeList = explode(',', $request->getQueryParam('scopeList'));
        }

        $userId = $request->getQueryParam('userId');
        $userIdList = $request->getQueryParam('userIdList');
        $teamIdList = $request->getQueryParam('teamIdList');

        $fetchParams = FetchParams
            ::create(
                DateTime::fromString($from),
                DateTime::fromString($to)
            )
            ->withScopeList($scopeList);

        if ($teamIdList) {
            $teamIdList = explode(',', $teamIdList);

            $raw = self::itemListToRaw(
                $this->calendarService->fetchForTeams($teamIdList, $fetchParams)
            );

            return ResponseComposer::json($raw);
        }

        if ($userIdList) {
            $userIdList = explode(',', $userIdList);

            $raw = self::itemListToRaw(
                $this->calendarService->fetchForUsers($userIdList, $fetchParams)
            );

            return ResponseComposer::json($raw);
        }

        if (!$userId) {
            $userId = $this->user->getId();
        }

        $fetchParams = $fetchParams
            ->withIsAgenda($isAgenda)
            ->withWorkingTimeRanges();

        $raw = self::itemListToRaw(
            $this->calendarService->fetch($userId, $fetchParams)
        );

        return ResponseComposer::json($raw);
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
