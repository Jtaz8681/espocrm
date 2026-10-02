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
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Mail\Exceptions\NoSmtp;
use Espo\Modules\Crm\Entities\MassEmail as MassEmailEntity;
use Espo\Modules\Crm\Tools\MassEmail\Service;

use Espo\Core\Acl\Table;
use Espo\Core\Api\Request;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;

use stdClass;

class MassEmail extends Record
{
    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NoSmtp
     * @throws NotFound
     */
    public function postActionSendTest(Request $request): bool
    {
        $id = $request->getParsedBody()->id ?? null;
        $targetList = $request->getParsedBody()->targetList ?? null;

        if (!$id || !is_array($targetList)) {
            throw new BadRequest();
        }

        $this->getMassEmailService()->processTest($id, $targetList);

        return true;
    }

    /**
     * @return stdClass[]
     * @throws Forbidden
     */
    public function getActionSmtpAccountDataList(): array
    {
        if (
            !$this->acl->checkScope(MassEmailEntity::ENTITY_TYPE, Table::ACTION_CREATE) &&
            !$this->acl->checkScope(MassEmailEntity::ENTITY_TYPE, Table::ACTION_EDIT)
        ) {
            throw new Forbidden();
        }

        return $this->getMassEmailService()->getSmtpAccountDataList();
    }

    private function getMassEmailService(): Service
    {
        return $this->injectableFactory->create(Service::class);
    }
}
