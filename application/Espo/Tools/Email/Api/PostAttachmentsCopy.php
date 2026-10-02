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

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Tools\Attachment\FieldData;
use Espo\Tools\Email\Service;

/**
 * Copies email attachments.
 */
class PostAttachmentsCopy implements Action
{
    public function __construct(private Service $service) {}

    public function process(Request $request): Response
    {
        $id = $request->getRouteParam('id');

        if (!$id) {
            throw new BadRequest();
        }

        $data = $request->getParsedBody();

        $field = $data->field ?? null;
        $parentType = $data->parentType ?? null;
        $relatedType = $data->relatedType ?? null;

        if (!$field) {
            throw new BadRequest("No `field`.");
        }

        try {
            $fieldData = new FieldData($field, $parentType, $relatedType);
        } catch (Error $e) {
            throw new BadRequest($e->getMessage());
        }

        $list = $this->service->copyAttachments($id, $fieldData);

        $ids = array_map(fn ($item) => $item->getId(), $list);

        $names = (object) [];

        foreach ($list as $item) {
            $names->{$item->getId()} = $item->getName();
        }

        return ResponseComposer::json([
            'ids' => $ids,
            'names' => $names,
        ]);
    }
}
