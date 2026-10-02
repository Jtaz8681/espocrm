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
use Espo\Tools\UserSecurity\Password\RecoveryService;

/**
 * Initiates a password recovery process.
 */
class PostPasswordChangeRequest implements Action
{
    public function __construct(private RecoveryService $service) {}

    public function process(Request $request): Response
    {
        $data = $request->getParsedBody();

        $userName = $data->userName ?? null;
        $emailAddress = $data->emailAddress ?? null;
        $url = $data->url ?? null;

        if (!$userName || !$emailAddress) {
            throw new BadRequest();
        }

        if (!is_string($userName) || !is_string($emailAddress)) {
            throw new BadRequest();
        }

        $this->service->request($emailAddress, $userName, $url);

        return ResponseComposer::json(true);
    }
}
