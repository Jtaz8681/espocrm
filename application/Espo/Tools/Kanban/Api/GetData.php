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

namespace Espo\Tools\Kanban\Api;

use Espo\Core\Api\Action as ActionAlias;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Tools\Kanban\KanbanService;

class GetData implements ActionAlias
{
    public function __construct(
        private KanbanService $service,
        private SearchParamsFetcher $searchParamsFetcher
    ) {}

    public function process(Request $request): Response
    {
        $entityType = $request->getRouteParam('entityType');

        if (!$entityType) {
            throw new BadRequest();
        }

        $searchParams = $this->searchParamsFetcher->fetch($request);

        $result = $this->service->getData($entityType, $searchParams);

        $list = [];

        foreach ($result->getGroups() as $group) {
            $list = [...$list, ...$group->collection->getValueMapList()];
        }

        return ResponseComposer::json([
            'total' => $result->getTotal(),
            'groups' => array_map(fn ($it) => $it->toRaw(), $result->getGroups()),
            'list' => $list,
        ]);
    }
}
