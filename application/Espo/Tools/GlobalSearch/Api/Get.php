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

namespace Espo\Tools\GlobalSearch\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Tools\GlobalSearch\Service;

/**
 * Global search.
 */
class Get implements Action
{
    public function __construct(
        private Service $service,
        private SearchParamsFetcher $searchParamsFetcher,
    ) {}

    public function process(Request $request): Response
    {
        $query = $request->getQueryParam('q');

        if ($query === null || $query === '') {
            throw new BadRequest("No `q` parameter.");
        }

        $searchParams = $this->searchParamsFetcher->fetch($request);

        $offset = $searchParams->getOffset();
        $maxSize = $searchParams->getMaxSize();

        $result = $this->service->find($query, $offset, $maxSize);

        return ResponseComposer::json($result->toApiOutput());
    }
}
