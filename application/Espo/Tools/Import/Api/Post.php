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

namespace Espo\Tools\Import\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Entities\Import;
use Espo\Tools\Import\Params as ImportParams;
use Espo\Tools\Import\Service;

/**
 * Creates imports.
 */
class Post implements Action
{
    public function __construct(private Service $service, private Acl $acl) {}

    public function process(Request $request): Response
    {
        if (!$this->acl->checkScope(Import::ENTITY_TYPE)) {
            throw new Forbidden();
        }

        $data = $request->getParsedBody();

        $entityType = $data->entityType ?? null;
        $attributeList = $data->attributeList ?? null;
        $attachmentId = $data->attachmentId ?? null;

        if (!is_array($attributeList)) {
            throw new BadRequest("No `attributeList`.");
        }

        if (!$attachmentId) {
            throw new BadRequest("No `attachmentId`.");
        }

        if (!$entityType) {
            throw new BadRequest("No `entityType`.");
        }

        $params = ImportParams::fromRaw($data);

        $result = $this->service->import($entityType, $attributeList, $attachmentId, $params);

        return ResponseComposer::json($result->getValueMap());
    }
}
