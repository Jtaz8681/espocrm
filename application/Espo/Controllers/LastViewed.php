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

use Espo\Core\Api\Request;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Tools\ActionHistory\Service as Service;

use stdClass;

/**
 * @noinspection PhpUnused
 */
class LastViewed
{
    public function __construct(private SearchParamsFetcher $searchParamsFetcher, private Service $service)
    {}

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function getActionIndex(Request $request): stdClass
    {
        $searchParams = $this->searchParamsFetcher->fetch($request);

        $offset = $searchParams->getOffset();
        $maxSize = $searchParams->getMaxSize();

        $result = $this->service->getLastViewed($maxSize, $offset);

        return $result->toApiOutput();
    }
}
