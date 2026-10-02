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
use Espo\Tools\Stream\FollowerRecordService;

/**
 * @noinspection PhpUnused
 */
class DeleteFollowers implements Action
{
    public function __construct(
        private FollowerRecordService $service,
        private Acl $acl
    ) {}

    public function process(Request $request): Response
    {
        $entityType = $request->getRouteParam('entityType');
        $id = $request->getRouteParam('id');

        $data = $request->getParsedBody();

        if (!$entityType || !$id) {
            throw new BadRequest();
        }

        if (!$this->acl->check($entityType)) {
            throw new Forbidden();
        }

        $ids = $data->ids ?? (isset($data->id) ? [$data->id] : []);

        if ($ids === [] || !is_array($ids)) {
            throw new BadRequest("No ids.");
        }

        foreach ($ids as $userId) {
            if (!is_string($userId)) {
                throw new BadRequest("Bad id item.");
            }
        }

        foreach ($ids as $userId) {
            $this->service->unlink($entityType, $id, $userId);
        }

        return ResponseComposer::json(true);
    }
}
