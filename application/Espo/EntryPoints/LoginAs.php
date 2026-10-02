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

namespace Espo\EntryPoints;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\EntryPoint\EntryPoint;
use Espo\Core\EntryPoint\Traits\NoAuth;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Utils\Client\ActionRenderer;

class LoginAs implements EntryPoint
{
    use NoAuth;

    public function __construct(private ActionRenderer $actionRenderer) {}

    /**
     * @throws BadRequest
     */
    public function run(Request $request, Response $response): void
    {
        $anotherUser = $request->getQueryParam('anotherUser');

        if (!$anotherUser) {
            throw new BadRequest("No anotherUser.");
        }

        $this->actionRenderer->write(
            $response,
            ActionRenderer\Params::create('controllers/login-as', 'login')
                ->withData([
                    'anotherUser' => $anotherUser,
                    'username' => $request->getQueryParam('username'),
                ])
                ->withInitAuth()
        );
    }
}
