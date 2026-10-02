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

namespace Espo\Tools\Stream\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Name\Field;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\Where\Item as WhereItem;
use Espo\Tools\Stream\GlobalRecordService;

/**
 * @noinspection PhpUnused
 */
class GetGlobal implements Action
{
    public function __construct(
        private Acl $acl,
        private SearchParamsFetcher $searchParamsFetcher,
        private GlobalRecordService $service
    ) {}

    public function process(Request $request): Response
    {
        if (!$this->acl->checkScope(GlobalRecordService::SCOPE_NAME)) {
            throw new Forbidden();
        }

        $searchParams = $this->fetchSearchParams($request);

        $result = $this->service->find($searchParams);

        return ResponseComposer::json($result->toApiOutput());
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    private function fetchSearchParams(Request $request): SearchParams
    {
        $searchParams = $this->searchParamsFetcher->fetch($request);

        $after = $request->getQueryParam('after');

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

        $beforeNumber = $request->getQueryParam('beforeNumber');

        if ($beforeNumber) {
            $searchParams = $searchParams
                ->withWhereAdded(
                    WhereItem
                        ::createBuilder()
                        ->setAttribute('number')
                        ->setType(WhereItem\Type::LESS_THAN)
                        ->setValue($beforeNumber)
                        ->build()
                );
        }

        return $searchParams;
    }
}
