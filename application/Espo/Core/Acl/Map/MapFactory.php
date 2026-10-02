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

namespace Espo\Core\Acl\Map;

use Espo\Entities\User;

use Espo\Core\Acl\Table;
use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingData;
use Espo\Core\InjectableFactory;

class MapFactory
{
    public function __construct(private InjectableFactory $injectableFactory)
    {}

    public function create(User $user, Table $table): Map
    {
        $bindingContainer = $this->createBindingContainer($user, $table);

        return $this->injectableFactory->createWithBinding(Map::class, $bindingContainer);
    }

    private function createBindingContainer(User $user, Table $table): BindingContainer
    {
        $bindingData = new BindingData();

        $binder = new Binder($bindingData);
        $binder
            ->bindInstance(User::class, $user)
            ->bindInstance(Table::class, $table)
            ->bindImplementation(CacheKeyProvider::class, DefaultCacheKeyProvider::class);

        return new BindingContainer($bindingData);
    }
}
