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

use Espo\Core\Api\Response;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Api\Request;
use Espo\Core\Utils\Json;
use Espo\Core\Utils\Metadata as MetadataUtil;
use Espo\Entities\User as UserEntity;
use Espo\Tools\App\MetadataService as Service;

use stdClass;

class Metadata
{
    private Service $service;
    private MetadataUtil $metadata;
    private UserEntity $user;

    public function __construct(
        Service $service,
        MetadataUtil $metadata,
        UserEntity $user
    ) {
        $this->service = $service;
        $this->metadata = $metadata;
        $this->user = $user;
    }

    public function getActionRead(Request $request): mixed
    {
        $key = $request->getQueryParam('key');

        if (is_string($key)) {
            return $this->service->getDataForFrontendByKey($key);
        }

        return $this->service->getDataForFrontend();
    }

    /**
     * @throws Forbidden
     */
    public function getActionGet(Request $request, Response $response): void
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }

        $key = $request->getQueryParam('key');

        $value = $this->metadata->get($key);

        $response->writeBody(Json::encode($value));
    }
}
