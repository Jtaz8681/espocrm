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
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Api\Request;
use Espo\Tools\App\SettingsService as Service;
use Espo\Entities\User;

use stdClass;

class Settings
{
    public function __construct(
        private Service $service,
        private User $user,
    ) {}

    public function getActionRead(): stdClass
    {
        return $this->getConfigData();
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     */
    public function putActionUpdate(Request $request): stdClass
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }

        $data = $request->getParsedBody();

        $this->service->setConfigData($data);

        return $this->getConfigData();
    }

    private function getConfigData(): stdClass
    {
        return $this->service->getConfigData();
    }
}
