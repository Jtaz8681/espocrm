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
use Espo\Core\Controllers\RecordBase;
use Espo\Core\Api\Request;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Tools\EmailFolder\Service;
use stdClass;

class EmailFolder extends RecordBase
{
    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function postActionMoveUp(Request $request): bool
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest();
        }

        $this->getEmailFolderService()->moveUp($data->id);

        return true;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function postActionMoveDown(Request $request): bool
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest();
        }

        $this->getEmailFolderService()->moveDown($data->id);

        return true;
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function getActionListAll(Request $request): stdClass
    {
        $userId = $request->getQueryParam('userId');

        $list = $this->getEmailFolderService()->listAll($userId);

        return (object) ['list' => $list];
    }

    private function getEmailFolderService(): Service
    {
        return $this->injectableFactory->create(Service::class);
    }
}
