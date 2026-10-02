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

namespace Espo\Tools\User\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\EntityProvider;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Tools\User\UsersAccessService;

/**
 * @noinspection PhpUnused
 */
class PostRecordUsersAccess implements Action
{
    public function __construct(
        private EntityProvider $entityProvider,
        private SearchParamsFetcher $searchParamsFetcher,
        private UsersAccessService $usersAccessService,
    ) {}

    public function process(Request $request): Response
    {
        $entityType = $request->getRouteParam('entityType') ?? throw new Forbidden();
        $id = $request->getRouteParam('id') ?? throw new Forbidden();
        $searchParams = $this->searchParamsFetcher->fetch($request);

        $entity = $this->entityProvider->get($entityType, $id);

        $result = $this->usersAccessService->get($entity, $searchParams);

        return ResponseComposer::json($result->toApiOutput());
    }
}
