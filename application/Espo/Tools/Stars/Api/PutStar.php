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

namespace Espo\Tools\Stars\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Tools\Stars\StarService;

/**
 * @noinspection PhpUnused
 */
class PutStar implements Action
{
    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl,
        private User $user,
        private StarService $service,
    ) {}

    public function process(Request $request): Response
    {
        $id = $request->getRouteParam('id');
        $entityType = $request->getRouteParam('entityType');

        if (!is_string($id) || !is_string($entityType)) {
            throw new BadRequest();
        }

        $entity = $this->getEntity($entityType, $id);

        $this->service->star($entity, $this->user);

        return ResponseComposer::json(true);
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    private function getEntity(string $entityType, string $id): Entity
    {
        $entity = $this->entityManager
            ->getRDBRepository($entityType)
            ->getById($id);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityRead($entity)) {
            throw new Forbidden();
        }

        if (!$this->user->isRegular() && !$this->user->isAdmin() && !$this->user->isPortal()) {
            throw new Forbidden("Not allowed for non-internal users.");
        }

        return $entity;
    }
}
