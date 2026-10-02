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

namespace Espo\Modules\Crm\Controllers;

use Espo\Core\Controllers\Record;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Api\Request;

use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Modules\Crm\Tools\Document\Service;
use Espo\Tools\Attachment\FieldData;
use stdClass;

class Document extends Record
{

    /**
     * @return stdClass[]
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function postActionGetAttachmentList(Request $request): array
    {
        $data = $request->getParsedBody();

        $id = $data->id ?? null;
        $field = $data->field ?? null;
        $parentType = $data->parentType ?? null;
        $relatedType = $data->relatedType ?? null;

        if (!$id || !$field) {
            throw new BadRequest("No `id` or `field`.");
        }

        try {
            $fieldData = new FieldData(
                $field,
                $parentType,
                $relatedType
            );
        } catch (Error $e) {
            throw new BadRequest($e->getMessage());
        }

        $attachment = $this->getDocumentService()->copyAttachment($id, $fieldData);

        return [$attachment->getValueMap()];
    }

    private function getDocumentService(): Service
    {
        return $this->injectableFactory->create(Service::class);
    }
}
