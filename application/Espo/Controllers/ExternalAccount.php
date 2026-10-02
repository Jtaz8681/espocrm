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

use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;
use Espo\Entities\ExternalAccount as ExternalAccountEntity;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Controllers\RecordBase;
use Espo\Core\Record\ReadParams;
use Espo\Tools\ExternalAccount\Service;
use stdClass;

class ExternalAccount extends RecordBase
{
    protected function checkAccess(): bool
    {
        return $this->acl->checkScope(ExternalAccountEntity::ENTITY_TYPE);
    }

    public function getActionList(Request $request, Response $response): stdClass
    {
       return  $this->createService()->getList();
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function getActionGetOAuth2Info(Request $request): ?stdClass
    {
        $id = $request->getQueryParam('id') ?? throw new BadRequest();

        return $this->createService()->getActionGetOAuth2Info($id);
    }

    public function getActionRead(Request $request, Response $response): stdClass
    {
        $id = $request->getRouteParam('id') ?: throw new BadRequest();

        return $this->getRecordService()
            ->read($id, ReadParams::create())
            ->getValueMap();
    }

    public function putActionUpdate(Request $request, Response $response): stdClass
    {
        $id = $request->getRouteParam('id') ?? throw new BadRequest();

        $data = $request->getParsedBody();

        return $this->createService()->update($id, $data);
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     * @throws Error
     * @throws BadRequest
     */
    public function postActionAuthorizationCode(Request $request): bool
    {
        $data = $request->getParsedBody();

        $id = $data->id ?? throw new BadRequest("No ID.");
        $code = $data->code ?? throw new BadRequest("No code.");

        $this->createService()->authorizationCode($id, $code);

        return true;
    }

    private function createService(): Service
    {
        return $this->injectableFactory->create(Service::class);
    }
}
