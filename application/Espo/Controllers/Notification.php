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

use Espo\Core\Name\Field;
use Espo\Tools\Notification\RecordService as Service;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Controllers\RecordBase;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\Where\Item as WhereItem;

use stdClass;

class Notification extends RecordBase
{
    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     */
    public function getActionList(Request $request, Response $response): stdClass
    {
        $searchParamsAux = $this->searchParamsFetcher->fetch($request);

        $offset = $searchParamsAux->getOffset();
        $maxSize = $searchParamsAux->getMaxSize();

        $after = $request->getQueryParam('after');
        $beforeNumber = $request->getQueryParam('beforeNumber');

        $searchParams = SearchParams
            ::create()
            ->withOffset($offset)
            ->withMaxSize($maxSize);

        if ($after) {
            $searchParams = $searchParams
                ->withWhereAdded(
                    WhereItem
                        ::createBuilder()
                        ->setAttribute(Field::CREATED_AT)
                        ->setType(WhereItem\Type::AFTER)
                        ->setValue($after)
                        ->build()
                );
        }

        $recordCollection = $this->getNotificationService()->get($this->user, $searchParams, $beforeNumber);

        return $recordCollection->toApiOutput();
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function getActionNotReadCount(): int
    {
        return $this->getNotificationService()->getNotReadCount($this->user);
    }

    public function postActionMarkAllRead(Request $request): bool
    {
        $userId = $this->user->getId();

        $this->getNotificationService()->markAllRead($userId);

        return true;
    }

    private function getNotificationService(): Service
    {
        return $this->injectableFactory->create(Service::class);
    }
}
