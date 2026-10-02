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

namespace Espo\Tools\Attachment\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Tools\Attachment\UploadService;

/**
 * Uploads attachment chunks.
 */
class PostChunk implements Action
{
    public function __construct(private UploadService $uploadService) {}

    public function process(Request $request): Response
    {
        $id = $request->getRouteParam('id');
        $body = $request->getBodyContents();

        if (!$id || !$body) {
            throw new BadRequest();
        }

        $this->uploadService->uploadChunk($id, $body);

        return ResponseComposer::json(true);
    }
}
