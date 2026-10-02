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
use Espo\Core\Api\Response;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Utils\Json;
use Espo\Tools\Oidc\Service;

class Oidc
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @throws Forbidden
     * @throws Error
     */
    public function getActionAuthorizationData(Request $request, Response $response): void
    {
        $data = $this->service->getAuthorizationData();

        $response->writeBody(Json::encode($data));
    }


    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function postActionBackchannelLogout(Request $request, Response $response): void
    {
        $token = $request->getParsedBody()->logout_token ?? null;

        if (!$token || !is_string($token)) {
            throw new BadRequest();
        }

        $this->service->backchannelLogout($token);

        $response->writeBody('true');
    }
}
