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

namespace Espo\Core\Action;

use Espo\Core\Acl;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\ReadParams;
use Espo\Core\Record\ReadResult;
use Espo\Core\Record\ServiceContainer as RecordServiceContainer;

use Espo\ORM\Entity;

use stdClass;

class Service
{
    public function __construct(
        private ActionFactory $factory,
        private Acl $acl,
        private RecordServiceContainer $recordServiceContainer
    ) {}

    /**
     * Perform an action.
     *
     * @throws Forbidden
     * @throws BadRequest
     * @throws NotFound
     * @throws Conflict
     */
    public function process(string $entityType, string $action, string $id, stdClass $data): ReadResult
    {
        if (!$this->acl->checkScope($entityType)) {
            throw new ForbiddenSilent();
        }

        if (!$action || !$id) {
            throw new BadRequest();
        }

        $actionParams = new Params($entityType, $id);

        $actionProcessor = $this->factory->create($action, $entityType);

        $actionProcessor->process(
            $actionParams,
            Data::fromRaw($data)
        );

        $service = $this->recordServiceContainer->get($entityType);

        return $service->read($id, ReadParams::create());
    }
}
