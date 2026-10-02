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

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\BadRequest;

use Espo\Core\Acl;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;

use Espo\Core\Exceptions\NotFound;
use Espo\Tools\DataPrivacy\Erasor;

class DataPrivacy
{
    /**
     * @throws Forbidden
     */
    public function __construct(private Erasor $erasor, private Acl $acl)
    {
        if ($this->acl->getPermissionLevel(Acl\Permission::DATA_PRIVACY) === Acl\Table::LEVEL_NO) {
            throw new Forbidden();
        }
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function postActionErase(Request $request, Response $response): void
    {
        $data = $request->getParsedBody();

        if (
            empty($data->entityType) ||
            empty($data->id) ||
            empty($data->fieldList) ||
            !is_array($data->fieldList)
        ) {
            throw new BadRequest();
        }

        $this->erasor->erase($data->entityType, $data->id, $data->fieldList);

        $response->writeBody('true');
    }
}
