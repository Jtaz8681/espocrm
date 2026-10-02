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

use Espo\Core\Acl\Table;
use Espo\Core\Controllers\Record;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Api\Request;
use Espo\Modules\Crm\Entities\CaseObj as CaseEntity;
use Espo\Modules\Crm\Tools\Case\Service;
use stdClass;

class CaseObj extends Record
{
    protected string $name = CaseEntity::ENTITY_TYPE;

    /**
     * @return stdClass[]
     * @throws BadRequest
     * @throws Forbidden
     */
    public function getActionEmailAddressList(Request $request): array
    {
        $id = $request->getQueryParam('id');

        if (!$id) {
            throw new BadRequest();
        }

        if (!$this->acl->checkScope(CaseEntity::ENTITY_TYPE, Table::ACTION_READ)) {
            throw new Forbidden();
        }

        $result = $this->injectableFactory
            ->create(Service::class)
            ->getEmailAddressList($id);

        return array_map(
            fn ($item) => $item->getValueMap(),
            $result
        );
    }
}
