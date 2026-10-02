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
use Espo\Core\Controllers\Record;
use Espo\Core\Api\Request;
use Espo\Tools\EmailFolder\GroupFolderService as Service;

class GroupEmailFolder extends Record
{
    /**
     * @throws BadRequest
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

    private function getEmailFolderService(): Service
    {
        /** @var Service */
        return $this->injectableFactory->create(Service::class);
    }
}
