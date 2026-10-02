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

namespace Espo\Core\Portal\Utils;

use Espo\Core\Api\Route as RouteItem;
use Espo\Core\Utils\Route as BaseRoute;

class Route extends BaseRoute
{
    public function getFullList(): array
    {
        $originalRouteList = parent::getFullList();

        $newRouteList = [];

        foreach ($originalRouteList as $route) {
            $path = $route->getAdjustedRoute();

            if ($path[0] !== '/') {
                $path = '/' . $path;
            }

            $path = '/{portalId}' . $path;

            $newRoute = new RouteItem(
                $route->getMethod(),
                $route->getRoute(),
                $path,
                $route->getParams(),
                $route->noAuth(),
                $route->getActionClassName()
            );

            $newRouteList[] = $newRoute;
        }

        return $newRouteList;
    }
}
