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
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Forbidden;

use Espo\Core\Exceptions\NotFound;
use Espo\Modules\Crm\Tools\Lead\Convert\Params as ConvertParams;
use Espo\Modules\Crm\Tools\Lead\Convert\Values;
use Espo\Modules\Crm\Tools\Lead\ConvertService;
use stdClass;

class Lead extends Record
{
    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Conflict
     * @throws NotFound
     */
    public function postActionConvert(Request $request): stdClass
    {
        $data = $request->getParsedBody();

        $id = $data->id ?? null;
        $records = $data->records ?? (object) [];

        if (!$id) {
            throw new BadRequest();
        }

        if (!$records instanceof stdClass) {
            throw new BadRequest();
        }

        $recordsPayload = Values::create();

        foreach (get_object_vars($records) as $entityType => $payload) {
            $recordsPayload = $recordsPayload->with($entityType, $payload);
        }

        $skipDuplicateCheck = $data->skipDuplicateCheck ?? false;

        $params = new ConvertParams($skipDuplicateCheck);

        $lead = $this->injectableFactory
            ->create(ConvertService::class)
            ->convert($id, $recordsPayload, $params);

        return $lead->getValueMap();
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function postActionGetConvertAttributes(Request $request): stdClass
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest();
        }

        $data = $this->injectableFactory
            ->create(ConvertService::class)
            ->getValues($data->id);

        return $data->getRaw();
    }
}
