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

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Tools\Kanban\KanbanService;

class PutOrder implements Action
{
    public function __construct(private KanbanService $service)
    {}

    public function process(Request $request): Response
    {
        $data = $request->getParsedBody();

        $entityType = $data->entityType ?? null;
        $group = $data->group ?? null;
        $ids = $data->ids ?? null;

        if (empty($entityType) || !is_string($entityType)) {
            throw new BadRequest();
        }

        if (empty($group) || !is_string($group)) {
            throw new BadRequest();
        }

        if (!is_array($ids)) {
            throw new BadRequest();
        }

        $this->service->order($entityType, $group, $ids);

        return ResponseComposer::json(true);
    }
}
