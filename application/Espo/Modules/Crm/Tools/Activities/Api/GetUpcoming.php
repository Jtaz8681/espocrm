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

namespace Espo\Modules\Crm\Tools\Activities\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Entities\User;
use Espo\Modules\Crm\Tools\Activities\Upcoming\Params;
use Espo\Modules\Crm\Tools\Activities\UpcomingService;

/**
 * Upcoming activities.
 *
 * @noinspection PhpUnused
 */
class GetUpcoming implements Action
{
    public function __construct(
        private User $user,
        private SearchParamsFetcher $searchParamsFetcher,
        private UpcomingService $service
    ) {}

    public function process(Request $request): Response
    {
        $userId = $request->getQueryParam('userId') ?? $this->user->getId();

        $params = $this->fetchParams($request);

        $result = $this->service->get($userId, $params);

        return ResponseComposer::json($result->toApiOutput());
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    private function fetchParams(Request $request): Params
    {
        $entityTypeList = $this->fetchEntityTypeList($request);
        $futureDays = $request->hasQueryParam('futureDays') ? intval($request->getQueryParam('futureDays')) : null;
        $searchParams = $this->searchParamsFetcher->fetch($request);

        return new Params(
            offset: $searchParams->getOffset(),
            maxSize: $searchParams->getMaxSize(),
            futureDays: $futureDays,
            entityTypeList: $entityTypeList,
            includeShared: $request->getQueryParam('includeShared') === 'true',
        );
    }

    /**
     * @return ?string[]
     * @throws BadRequest
     */
    private function fetchEntityTypeList(Request $request): ?array
    {
        $entityTypeList = $request->getQueryParams()['entityTypeList'] ?? null;

        if (!is_array($entityTypeList) && $entityTypeList !== null) {
            throw new BadRequest("Bad entityTypeList.");
        }

        foreach ($entityTypeList ?? [] as $it) {
            if (!is_string($it)) {
                throw new BadRequest("Bad item in entityTypeList.");
            }
        }

        return $entityTypeList;
    }
}
