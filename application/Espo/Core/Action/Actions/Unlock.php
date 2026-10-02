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

namespace Espo\Core\Action\Actions;

use Espo\Core\Action\Action;
use Espo\Core\Action\Data;
use Espo\Core\Action\Params;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Record\EntityProvider;
use Espo\Tools\Lock\LockService;

/**
 * @noinspection PhpUnused
 */
class Unlock implements Action
{
    public function __construct(
        private LockService $lockService,
        private EntityProvider $entityProvider,
    ) {}

    public function process(Params $params, Data $data): void
    {
        $entityType = $params->getEntityType();
        $id = $params->getId();

        if (!$this->lockService->isEnabled($entityType)) {
            throw new ForbiddenSilent("Not enabled.");
        }

        if (!$this->lockService->isAllowed($entityType)) {
            throw new ForbiddenSilent("Not allowed.");
        }

        $entity = $this->entityProvider->get($entityType, $id);

        try {
            $this->lockService->unlock($entity);
        } catch (Error $e) {
            throw new Forbidden(previous: $e);
        }
    }
}
