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
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Modules\Crm\Tools\Activities\Service as Service;

/**
 * Activities of specific entity type related to a record.
 */
class GetListTyped implements Action
{
    public function __construct(
        private SearchParamsFetcher $searchParamsFetcher,
        private Service $service
    ) {}

    public function process(Request $request): Response
    {
        $parentType = $request->getRouteParam('parentType');
        $id = $request->getRouteParam('id');
        $type = $request->getRouteParam('type');
        $targetType = $request->getRouteParam('targetType');

        if (
            !$parentType ||
            !$id ||
            !$type ||
            !$targetType
        ) {
            throw new BadRequest();
        }

        if ($type === 'activities') {
            $isHistory = false;
        } else if ($type === 'history') {
            $isHistory = true;
        } else {
            throw new BadRequest("Bad type.");
        }

        $searchParams = $this->searchParamsFetcher->fetch($request);

        $result = $this->service->findActivitiesEntityType(
            $parentType,
            $id,
            $targetType,
            $isHistory,
            $searchParams
        );

        return ResponseComposer::json($result->toApiOutput());
    }
}
