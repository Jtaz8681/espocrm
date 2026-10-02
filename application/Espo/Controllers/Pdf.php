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

namespace Espo\Controllers;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;

use Espo\Core\Acl;
use Espo\Core\Api\Request;

use Espo\Core\Exceptions\NotFound;
use Espo\Entities\Template as TemplateEntity;
use Espo\Tools\Pdf\MassService;

use stdClass;

class Pdf
{
    private MassService $service;
    private Acl $acl;

    public function __construct(MassService $service, Acl $acl)
    {
        $this->service = $service;
        $this->acl = $acl;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function postActionMassPrint(Request $request): stdClass
    {
        $data = $request->getParsedBody();

        if (empty($data->idList) || !is_array($data->idList)) {
            throw new BadRequest();
        }

        if (empty($data->entityType)) {
            throw new BadRequest();
        }

        if (empty($data->templateId)) {
            throw new BadRequest();
        }

        if (!$this->acl->checkScope(TemplateEntity::ENTITY_TYPE)) {
            throw new Forbidden();
        }

        if (!$this->acl->checkScope($data->entityType)) {
            throw new Forbidden();
        }

        $id = $this->service->generate($data->entityType, $data->idList, $data->templateId);

        return (object) [
            'id' => $id,
        ];
    }
}
