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

namespace Espo\Tools\Stream\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\EntityProvider;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\ORM\Entity;
use Espo\Tools\Stream\RecordService;

/**
 * @noinspection PhpUnused
 */
class GetStreamAttachments implements Action
{
    public function __construct(
        private EntityProvider $entityProvider,
        private Acl $acl,
        private SearchParamsFetcher $searchParamsFetcher,
        private RecordService $service,
    ) {}

    public function process(Request $request): Response
    {
        $entity = $this->getEntity($request);
        $searchParams = $this->searchParamsFetcher->fetch($request);

        $result = $this->service->findAttachments($entity, $searchParams);

        return ResponseComposer::json($result->toApiOutput());
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     * @throws BadRequest
     */
    private function getEntity(Request $request): Entity
    {
        $entityType = $request->getRouteParam('entityType') ?? throw new BadRequest();
        $id = $request->getRouteParam('id') ?? throw new BadRequest();

        $entity = $this->entityProvider->get($entityType, $id);

        if (!$this->acl->checkEntityStream($entity)) {
            throw new Forbidden("No 'stream' access.");
        }

        return $entity;
    }
}
