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

namespace Espo\Controllers;

use Espo\Core\Api\Request;
use Espo\Core\Exceptions\BadRequest;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Tools\App\PreferencesService as Service;

use stdClass;

class Preferences
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function getActionRead(Request $request): stdClass
    {
        $userId = $request->getRouteParam('id');

        if (!$userId) {
            throw new BadRequest();
        }

        return $this->service->read($userId)->getValueMap();
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function deleteActionDelete(Request $request): stdClass
    {
        $userId = $request->getRouteParam('id');

        if (!$userId) {
            throw new BadRequest();
        }

        $this->service->resetToDefaults($userId);

        return $this->service
            ->read($userId)
            ->getValueMap();
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function putActionUpdate(Request $request): stdClass
    {
        $userId = $request->getRouteParam('id');

        if (!$userId) {
            throw new BadRequest();
        }

        $data = $request->getParsedBody();

        return $this->service
            ->update($userId, $data)
            ->getValueMap();
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function postActionResetDashboard(Request $request): stdClass
    {
        $data = $request->getParsedBody();

        $userId = $data->id ?? null;

        if (!$userId) {
            throw new BadRequest();
        }

        return $this->service->resetDashboard($userId);
    }
}
