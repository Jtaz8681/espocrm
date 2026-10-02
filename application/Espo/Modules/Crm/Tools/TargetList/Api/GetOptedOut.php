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

namespace Espo\Modules\Crm\Tools\TargetList\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Modules\Crm\Entities\TargetList;
use Espo\Modules\Crm\Tools\TargetList\OptOutService;

/**
 * @noinspection PhpUnused
 */
class GetOptedOut implements Action
{
    public function __construct(
        private SearchParamsFetcher $searchParamsFetcher,
        private Acl $acl,
        private OptOutService $service
    ) {}

    public function process(Request $request): Response
    {
        $id = $request->getRouteParam('id');

        if (!$id) {
            throw new BadRequest();
        }

        if (!$this->acl->check(TargetList::ENTITY_TYPE)) {
            throw new Forbidden();
        }

        $searchParams = $this->searchParamsFetcher->fetch($request);

        $result = $this->service->find($id, $searchParams);

        return ResponseComposer::json($result->toApiOutput());
    }
}
