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

namespace Espo\Modules\Crm\Controllers;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Api\Request;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Controllers\Record;
use Espo\Modules\Crm\Tools\TargetList\OptOutService;
use Espo\Modules\Crm\Tools\TargetList\RecordService;

class TargetList extends Record
{
    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function postActionUnlinkAll(Request $request): bool
    {
        $data = $request->getParsedBody();

        $id = $data->id ?? null;
        $link = $data->link ?? null;

        if (
            !is_string($id) ||
            !is_string($link)
        ) {
            throw new BadRequest();
        }

        $this->injectableFactory->create(RecordService::class)->unlinkAll($id, $link);

        return true;
    }

    /**
     * @throws BadRequest
     * @throws NotFound
     * @throws Forbidden
     */
    public function postActionOptOut(Request $request): bool
    {
        $data = $request->getParsedBody();

        $id = $data->id ?? null;
        $targetType = $data->targetType ?? null;
        $targetId = $data->targetId ?? null;

        if (
            !is_string($id) ||
            !is_string($targetType) ||
            !is_string($targetId)
        ) {
            throw new BadRequest();
        }

        $this->getOptOutService()->optOut($id, $targetType, $targetId);

        return true;
    }

    /**
     * @throws BadRequest
     * @throws NotFound
     * @throws Forbidden
     */
    public function postActionCancelOptOut(Request $request): bool
    {
        $data = $request->getParsedBody();

        $id = $data->id ?? null;
        $targetType = $data->targetType ?? null;
        $targetId = $data->targetId ?? null;

        if (
            !is_string($id) ||
            !is_string($targetType) ||
            !is_string($targetId)
        ) {
            throw new BadRequest();
        }

        $this->getOptOutService()->cancelOptOut($id, $targetType, $targetId);

        return true;
    }

    private function getOptOutService(): OptOutService
    {
        return $this->injectableFactory->create(OptOutService::class);
    }
}
