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

namespace Espo\Modules\Crm\Tools\Campaign\Api;

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Modules\Crm\Entities\Campaign;
use Espo\Modules\Crm\Tools\Campaign\MailMergeService;

/**
 * Generates mail merge PDFs.
 */
class PostGenerateMailMerge implements Action
{
    public function __construct(
        private MailMergeService $service,
        private Acl $acl
    ) {}

    public function process(Request $request): Response
    {
        $id = $request->getRouteParam('id');
        $link = $request->getParsedBody()->link ?? null;

        if (!$id) {
            throw new BadRequest();
        }

        if (!$link) {
            throw new BadRequest("No `link`.");
        }

        if (!$this->acl->checkScope(Campaign::ENTITY_TYPE, Table::ACTION_READ)) {
            throw new Forbidden();
        }

        $attachmentId = $this->service->generate($id, $link);

        return ResponseComposer::json(['id' => $attachmentId]);
    }
}
