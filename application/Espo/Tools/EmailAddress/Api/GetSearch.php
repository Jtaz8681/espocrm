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

namespace Espo\Tools\EmailAddress\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Config;
use Espo\Entities\Email;
use Espo\Tools\Email\AddressService;

/**
 * Searches email addresses.
 * @noinspection PhpUnused
 */
class GetSearch implements Action
{
    private const ADDRESS_MAX_SIZE = 50;

    public function __construct(
        private AddressService $service,
        private Acl $acl,
        private Config\ApplicationConfig $applicationConfig,
    ) {}

    public function process(Request $request): Response
    {
        if (!$this->acl->checkScope(Email::ENTITY_TYPE)) {
            throw new Forbidden();
        }

        $entityType = $request->getQueryParam('entityType');
        $q = $request->getQueryParam('q');
        $onlyActual = $request->getQueryParam('onlyActual') === 'true';
        $maxSize = intval($request->getQueryParam('maxSize'));

        if (!$entityType && !$this->acl->checkScope(Email::ENTITY_TYPE, Acl\Table::ACTION_CREATE)) {
            throw new Forbidden("No 'create' access for Email.");
        }

        if ($entityType && !$this->acl->checkScope($entityType, Acl\Table::ACTION_READ)) {
            throw new Forbidden("No 'read' access for entity type.");
        }

        if (is_string($q)) {
            $q = trim($q);
        }

        if (!$q) {
            throw new BadRequest("No `q` parameter.");
        }

        if (!$maxSize || $maxSize > self::ADDRESS_MAX_SIZE) {
            $maxSize = $this->applicationConfig->getRecordsPerPage();
        }

        if ($entityType) {
            $result = $this->service->searchInEntityType($entityType, $q, $maxSize);

            return ResponseComposer::json($result);
        }

        $result = $this->service->searchInAddressBook($q, $maxSize, $onlyActual);

        return ResponseComposer::json($result);
    }
}
