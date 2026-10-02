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

namespace Espo\Tools\Pipeline\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\EntityProvider;
use Espo\Core\Select\SearchParams;
use Espo\Core\Utils\Json;
use Espo\Entities\Pipeline;
use Espo\Tools\Pipeline\MoveService;

/**
 * @noinspection PhpUnused
 */
class PostMove implements Action
{
    public function __construct(
        private EntityProvider $entityProvider,
        private Acl $acl,
        private MoveService $moveService,
    ) {}

    public function process(Request $request): Response
    {
        $entity = $this->getEntity($request);
        $type = $this->fetchType($request);
        $searchParams = $this->fetchSearchParams($request);

        $this->moveService->move($entity, $type, $searchParams);

        return ResponseComposer::json(true);
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    private function getEntity(Request $request): Pipeline
    {
        $id = $request->getRouteParam('id') ?? throw new BadRequest();

        if (!$this->acl->checkScope(Pipeline::ENTITY_TYPE)) {
            throw new Forbidden();
        }

        $entity = $this->entityProvider->getByClass(Pipeline::class, $id);

        if (!$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden();
        }

        return $entity;
    }

    /**
     * @return MoveService::TYPE_TOP|MoveService::TYPE_UP|MoveService::TYPE_DOWN|MoveService::TYPE_BOTTOM
     * @throws BadRequest
     */
    private function fetchType(Request $request): string
    {
        $type = $request->getRouteParam('type') ?? throw new BadRequest();

        if (
            !in_array($type, [
                MoveService::TYPE_TOP,
                MoveService::TYPE_UP,
                MoveService::TYPE_DOWN,
                MoveService::TYPE_BOTTOM,
            ])
        ) {
            throw new BadRequest("Bad type.");
        }

        return $type;
    }

    private function fetchSearchParams(Request $request): SearchParams
    {
        $body = $request->getParsedBody();

        $searchParams = SearchParams::create();

        if ($body->whereGroup ?? null) {
            $rawWhere = Json::decode(Json::encode($body->whereGroup), true);

            $searchParams = SearchParams::fromRaw(['where' => $rawWhere]);
        }

        return $searchParams;
    }
}
