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

use Espo\Core\Exceptions\BadRequest;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Tools\Dashboard\Service;

use Espo\Core\Api\Request;
use Espo\Core\Controllers\Record;

class DashboardTemplate extends Record
{
    protected function checkAccess(): bool
    {
        return $this->user->isAdmin();
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function postActionDeployToUsers(Request $request): bool
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest();
        }

        if (empty($data->userIdList)) {
            throw new BadRequest();
        }

        $this->getDashboardTemplateService()->deployTemplateToUsers(
            $data->id,
            $data->userIdList,
            !empty($data->append)
        );

        return true;
    }

    /**
     * @throws BadRequest
     * @throws NotFound
     */
    public function postActionDeployToTeam(Request $request): bool
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest();
        }

        if (empty($data->teamId)) {
            throw new BadRequest();
        }

        $this->getDashboardTemplateService()->deployTemplateToTeam(
            $data->id,
            $data->teamId,
            !empty($data->append)
        );

        return true;
    }

    private function getDashboardTemplateService(): Service
    {
        return $this->injectableFactory->create(Service::class);
    }
}
