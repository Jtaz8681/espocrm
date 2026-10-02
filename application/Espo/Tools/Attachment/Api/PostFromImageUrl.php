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
use Espo\Core\Exceptions\Error;
use Espo\Tools\Attachment\FieldData;
use Espo\Tools\Attachment\UploadUrlService;

/**
 * Crates attachments from image URLs.
 */
class PostFromImageUrl implements Action
{
    public function __construct(private UploadUrlService $uploadUrlService) {}

    public function process(Request $request): Response
    {
        $data = $request->getParsedBody();

        $url = $data->url ?? null;
        $field = $data->field ?? null;
        $parentType = $data->parentType ?? null;
        $relatedType = $data->relatedType ?? null;

        if (!$url || !$field) {
            throw new BadRequest("No `url` or `field`.");
        }

        try {
            $fieldData = new FieldData($field, $parentType, $relatedType);
        } catch (Error $e) {
            throw new BadRequest($e->getMessage());
        }

        $attachment = $this->uploadUrlService->uploadImage($url, $fieldData);

        return ResponseComposer::json($attachment->getValueMap());
    }
}
