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

namespace Espo\Tools\Email\Api;

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\Forbidden;
use Espo\Entities\Email as EmailEntity;
use Espo\Tools\EmailTemplate\InsertField\Service as InsertFieldService;

class GetInsertFieldData implements Action
{
    public function __construct(
        private InsertFieldService $service,
        private Acl $acl
    ) {}

    public function process(Request $request): Response
    {
        if (!$this->acl->checkScope(EmailEntity::ENTITY_TYPE, Table::ACTION_CREATE)) {
            throw new Forbidden();
        }

        $data = $this->service->getData(
            $request->getQueryParam('parentType'),
            $request->getQueryParam('parentId'),
            $request->getQueryParam('to')
        );

        return ResponseComposer::json($data);
    }
}
