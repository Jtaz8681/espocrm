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

use Espo\Core\AclManager;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Entities\User;
use Espo\ORM\EntityManager;

/**
 * User ACL data.
 */
class GetUserAcl implements Action
{
    public function __construct(
        private EntityManager $entityManager,
        private AclManager $aclManager,
        private User $user
    ) {}

    public function process(Request $request): Response
    {
        $userId = $request->getRouteParam('id');

        if (!$userId) {
            throw new BadRequest();
        }

        if (
            !$this->user->isAdmin() &&
            $this->user->getId() !== $userId
        ) {
            throw new Forbidden();
        }

        $user = $this->entityManager->getEntityById(User::ENTITY_TYPE, $userId);

        if (!$user) {
            throw new NotFound();
        }

        $data = $this->aclManager->getMapData($user);

        return ResponseComposer::json($data);
    }
}
