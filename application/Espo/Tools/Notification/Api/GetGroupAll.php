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

namespace Espo\Tools\Notification\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Core\Select\Where\Item as WhereItem;
use Espo\Entities\Notification;
use Espo\Tools\Notification\GroupAllService;

/**
 * @noinspection PhpUnused
 */
class GetGroupAll implements Action
{
    public function __construct(
        private GroupAllService $service,
        private SearchParamsFetcher $searchParamsFetcher,
    ) {}

    public function process(Request $request): Response
    {
        $type = $request->getQueryParam('type') ?? throw new BadRequest("No `type`.");
        $id = $request->getQueryParam('id') ?? throw new BadRequest("No `id`.");

        $searchParams = $this->searchParamsFetcher->fetch($request);

        $beforeNumber = $request->getQueryParam('beforeNumber');

        if ($beforeNumber) {
            $searchParams = $searchParams
                ->withWhereAdded(
                    WhereItem
                        ::createBuilder()
                        ->setAttribute(Notification::ATTR_NUMBER)
                        ->setType(WhereItem\Type::LESS_THAN)
                        ->setValue($beforeNumber)
                        ->build()
                );
        }

        $collection = $this->service->get($type, $id, $searchParams);

        return ResponseComposer::json(
            $collection->toApiOutput()
        );
    }
}
