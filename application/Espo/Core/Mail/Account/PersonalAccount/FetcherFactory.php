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

namespace Espo\Core\Mail\Account\PersonalAccount;

use Espo\Core\Binding\Factory;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\InjectableFactory;
use Espo\Core\Mail\Account\Hook\AfterFetch;
use Espo\Core\Mail\Account\PersonalAccount\Hooks\AfterFetch as PersonalAccountAfterFetch;
use Espo\Core\Mail\Account\Fetcher;
use Espo\Core\Mail\Account\StorageFactory;
use Espo\Core\Mail\Account\PersonalAccount\StorageFactory as PersonalAccountStorageFactory;

/**
 * @implements Factory<Fetcher>
 */
class FetcherFactory implements Factory
{
    public function __construct(private InjectableFactory $injectableFactory)
    {}

    public function create(): Fetcher
    {
        $binding = BindingContainerBuilder::create()
            ->bindImplementation(StorageFactory::class, PersonalAccountStorageFactory::class)
            ->bindImplementation(AfterFetch::class, PersonalAccountAfterFetch::class)
            ->build();

        return $this->injectableFactory->createWithBinding(Fetcher::class, $binding);
    }
}
