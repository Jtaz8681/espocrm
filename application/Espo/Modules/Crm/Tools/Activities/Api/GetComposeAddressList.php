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

namespace Espo\Modules\Crm\Tools\Activities\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Modules\Crm\Tools\Activities\ComposeEmailService;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @noinspection PhpUnused
 */
class GetComposeAddressList implements Action
{
    public function __construct(
        private Acl $acl,
        private EntityManager $entityManager,
        private ComposeEmailService $service
    ) {}

    public function process(Request $request): Response
    {
        $parentType = $request->getRouteParam('parentType');
        $id = $request->getRouteParam('id');

        if (!$parentType || !$id) {
            throw new BadRequest();
        }

        $entity = $this->fetchEntity($parentType, $id);

        $list = $this->service->getEmailAddressList($entity);

        return ResponseComposer::json(
            array_map(fn ($item) => $item->getValueMap(), $list)
        );
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    private function fetchEntity(string $parentType, string $id): Entity
    {
        $entity = $this->entityManager->getEntityById($parentType, $id);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityRead($entity)) {
            throw new Forbidden();
        }

        return $entity;
    }
}
