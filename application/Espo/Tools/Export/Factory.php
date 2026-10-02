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

namespace Espo\Tools\Export;

use Espo\Core\InjectableFactory;
use Espo\Entities\User;

use Espo\Core\AclManager;
use Espo\Core\Acl;

use Espo\Core\Binding\BindingContainerBuilder;

class Factory
{
    private InjectableFactory $injectableFactory;

    private AclManager $aclManager;

    public function __construct(InjectableFactory $injectableFactory, AclManager $aclManager)
    {
        $this->injectableFactory = $injectableFactory;
        $this->aclManager = $aclManager;
    }

    public function create(): Export
    {
        return $this->injectableFactory->create(Export::class);
    }

    public function createForUser(User $user): Export
    {
        $bindingContainer = BindingContainerBuilder::create()
            ->bindInstance(User::class, $user)
            ->bindInstance(Acl::class, $this->aclManager->createUserAcl($user))
            ->build();

        return $this->injectableFactory->createWithBinding(Export::class, $bindingContainer);
    }
}
