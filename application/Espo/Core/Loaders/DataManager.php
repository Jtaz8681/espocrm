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

namespace Espo\Core\Loaders;

use Espo\Core\Container\Loader;
use Espo\Core\DataManager as DataManagerService;
use Espo\Core\InjectableFactory;

class DataManager implements Loader
{

    public function __construct(private InjectableFactory $injectableFactory)
    {}

    public function load(): DataManagerService
    {
        return $this->injectableFactory->create(DataManagerService::class);
    }
}
