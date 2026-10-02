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

namespace Espo\Tools\UserSecurity\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Entities\User;
use Espo\Tools\UserSecurity\Password\Service;
use SensitiveParameter;

/**
 * Changes own user password.
 */
class PutPassword implements Action
{
    public function __construct(
        private Service $service,
        private User $user
    ) {}

    public function process(#[SensitiveParameter] Request $request): Response
    {
        $data = $request->getParsedBody();

        $password = $data->password ?? null;
        $currentPassword = $data->currentPassword ?? null;

        if (
            !is_string($password) ||
            !is_string($currentPassword)
        ) {
            throw new BadRequest("No `password` or `currentPassword`.");
        }

        $this->service->changePasswordWithCheck($this->user->getId(), $password, $currentPassword);

        return ResponseComposer::json(true);
    }
}
