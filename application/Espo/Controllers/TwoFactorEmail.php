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
use Espo\Tools\UserSecurity\TwoFactor\EmailService as Service;

use Espo\Entities\User;

class TwoFactorEmail
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
     * @throws Error
     * @throws NotFound
     */
    public function postActionSendCode(Request $request): bool
    {
        $data = $request->getParsedBody();

        $id = $data->id ?? null;
        $emailAddress = $data->emailAddress ?? null;

        if (!$id) {
            throw new BadRequest("No 'id'.");
        }

        if (!$emailAddress) {
            throw new BadRequest("No 'emailAddress'.");
        }

        if (!$this->user->isAdmin() && $id !== $this->user->getId()) {
            throw new Forbidden();
        }

        $this->service->sendCode($id, $emailAddress);

        return true;
    }
}
