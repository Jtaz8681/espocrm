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

use Espo\Core\Exceptions\NotFound;
use Espo\Services\Integration as Service;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Api\Request;
use Espo\Entities\User;

use stdClass;

class Integration
{

    /**
     * @throws Forbidden
     */
    public function __construct(
        private Service $service,
        private User $user,
    ) {

        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function getActionRead(Request $request): stdClass
    {
        /** @var string $id */
        $id = $request->getRouteParam('id');

        $entity = $this->service->read($id);

        return $entity->getValueMap();
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function putActionUpdate(Request $request): stdClass
    {
        /** @var string $id */
        $id = $request->getRouteParam('id');
        $data = $request->getParsedBody();

        $entity = $this->service->update($id, $data);

        return $entity->getValueMap();
    }
}
