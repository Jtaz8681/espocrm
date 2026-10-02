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

use Espo\Core\Api\Response;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Api\Request;
use Espo\Core\Controllers\Record;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\Where\Item as WhereItem;
use stdClass;

class User extends Record
{
    public function postActionCreate(Request $request, Response $response): stdClass
    {
        if ($request->getHeader('Content-Type') !== 'application/json') {
            throw new BadRequest("Not supported content type.");
        }

        return parent::postActionCreate($request, $response);
    }

    public function postActionCreateLink(Request $request): bool
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }

        return parent::postActionCreateLink($request);
    }

    public function deleteActionRemoveLink(Request $request): bool
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }

        return parent::deleteActionRemoveLink($request);
    }

    protected function fetchSearchParamsFromRequest(Request $request): SearchParams
    {
        $searchParams = parent::fetchSearchParamsFromRequest($request);

        $userType = $request->getQueryParam('userType');

        if (!$userType) {
            return $searchParams;
        }

        return $searchParams->withWhereAdded(
            WhereItem::fromRaw([
                'type' => 'isOfType',
                'attribute' => 'id',
                'value' => $userType,
            ])
        );
    }
}
