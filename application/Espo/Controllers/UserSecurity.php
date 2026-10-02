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

use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\BadRequest;

use Espo\Core\Api\Request;

use Espo\Core\Exceptions\NotFound;
use Espo\Tools\UserSecurity\Service as Service;

use Espo\Entities\User;

use stdClass;

class UserSecurity
{
    private Service $service;
    private User $user;

    /**
     * @throws Forbidden
     */
    public function __construct(Service $service, User $user)
    {
        $this->service = $service;
        $this->user = $user;

        if (
            !$this->user->isAdmin() &&
            !$this->user->isRegular() &&
            !$this->user->isPortal()
        ) {
            throw new Forbidden();
        }
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function getActionRead(Request $request): stdClass
    {
        $id = $request->getRouteParam('id');

        if (!$id) {
            throw new BadRequest();
        }

        if (!$this->user->isAdmin() && $id !== $this->user->getId()) {
            throw new Forbidden();
        }

        return $this->service->read($id);
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function postActionGetTwoFactorUserSetupData(Request $request): stdClass
    {
        $data = $request->getParsedBody();

        $id = $data->id ?? null;

        if (!$id) {
            throw new BadRequest("No 'id'.");
        }

        if (!$this->user->isAdmin() && $id !== $this->user->getId()) {
            throw new Forbidden();
        }

        return $this->service->getTwoFactorUserSetupData($id, $data);
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function putActionUpdate(Request $request): stdClass
    {
        $id = $request->getRouteParam('id');

        $data = $request->getParsedBody();

        if (!$id) {
            throw new BadRequest();
        }

        if (!$this->user->isAdmin() && $id !== $this->user->getId()) {
            throw new Forbidden();
        }

        return $this->service->update($id, $data);
    }
}
