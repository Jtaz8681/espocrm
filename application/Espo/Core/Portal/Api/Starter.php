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

namespace Espo\Core\Portal\Api;

use Espo\Core\Api\MiddlewareProvider;
use Espo\Core\Api\Starter as StarterBase;
use Espo\Core\ApplicationState;
use Espo\Core\Portal\Utils\Route as RouteUtil;
use Espo\Core\Api\RouteProcessor;
use Espo\Core\Api\Route\RouteParamsFetcher;
use Espo\Core\Utils\Config\SystemConfig;
use Espo\Core\Utils\Log;

class Starter extends StarterBase
{
    public function __construct(
        RouteProcessor $requestProcessor,
        RouteUtil $routeUtil,
        RouteParamsFetcher $routeParamsFetcher,
        MiddlewareProvider $middlewareProvider,
        Log $log,
        SystemConfig $systemConfig,
        ApplicationState $applicationState
    ) {
        $part = basename($applicationState->getPortalId());

        $routeCacheFile = 'data/cache/application/slim-routes-portal-' . $part . '.php';

        parent::__construct(
            $requestProcessor,
            $routeUtil,
            $routeParamsFetcher,
            $middlewareProvider,
            $log,
            $systemConfig,
            $routeCacheFile
        );
    }
}
