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
use Espo\Core\Controllers\Record;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use stdClass;

class AppLogRecord extends Record
{
    protected function checkAccess(): bool
    {
        if (!$this->user->isAdmin()) {
            return false;
        }

        if (!$this->config->get('restrictedMode')) {
            return true;
        }

        if ($this->config->get('appLogAdminAllowed')) {
            return true;
        }

        return $this->user->isSuperAdmin();
    }

    public function postActionCreate(Request $request, Response $response): stdClass
    {
        throw new Forbidden();
    }

    public function putActionUpdate(Request $request, Response $response): stdClass
    {
        throw new Forbidden();
    }

    public function postActionCreateLink(Request $request): bool
    {
        throw new Forbidden();
    }

    public function deleteActionRemoveLink(Request $request): bool
    {
        throw new Forbidden();
    }
}
