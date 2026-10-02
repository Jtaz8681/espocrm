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

declare(strict_types=1);

namespace Espo\Tools\EmailAccount\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Field\Date;
use Espo\Core\Record\EntityProvider;
use Espo\Core\Record\ServiceContainer;
use Espo\Entities\EmailAccount;
use Espo\Entities\InboundEmail;
use Espo\Tools\EmailAccount\ResetService;
use InvalidArgumentException;

/**
 * @noinspection PhpUnused
 */
class PostResetFetchData implements Action
{
    public function __construct(
        private EntityProvider $entityProvider,
        private Acl $acl,
        private ResetService $service,
        private ServiceContainer $serviceContainer,
    ) {}

    public function process(Request $request): Response
    {
        $entityType = $request->getRouteParam('entityType');
        $id = $request->getRouteParam('id') ?? throw new BadRequest();
        $fetchSinceRaw = $request->getParsedBody()->fetchSince ?? null;

        if (!is_string($fetchSinceRaw)) {
            throw new BadRequest("No or bad 'fetchSince'.");
        }

        try {
            $fetchSince = Date::fromString($fetchSinceRaw);
        } catch (InvalidArgumentException) {
            throw new BadRequest("Bad date.");
        }

        if ($entityType === EmailAccount::ENTITY_TYPE) {
            $entity = $this->entityProvider->getByClass(EmailAccount::class, $id);
        } else if ($entityType === InboundEmail::ENTITY_TYPE) {
            $entity = $this->entityProvider->getByClass(InboundEmail::class, $id);
        } else {
            throw new BadRequest();
        }

        if (!$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden("No edit access.");
        }

        $this->service->reset($entity, $fetchSince);

        $this->serviceContainer->get($entityType)->prepareEntityForOutput($entity);

        return ResponseComposer::json($entity->getValueMap());
    }
}
