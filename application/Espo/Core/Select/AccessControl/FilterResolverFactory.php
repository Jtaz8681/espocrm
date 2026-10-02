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

namespace Espo\Core\Select\AccessControl;

use Espo\Core\AclManager;
use Espo\Core\InjectableFactory;
use Espo\Core\Acl;
use Espo\Core\Portal\Acl as PortalAcl;
use Espo\Core\Portal\AclManager as PortalAclManager;
use Espo\Core\Utils\Metadata;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingData;
use Espo\Entities\User;

class FilterResolverFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata,
        private AclManager $aclManager,
        private Acl $acl,
    ) {}

    public function create(string $entityType, User $user): FilterResolver
    {
        $className = !$user->isPortal() ?
            $this->getClassName($entityType) :
            $this->getPortalClassName($entityType);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder
            ->bindInstance(User::class, $user)
            ->bindInstance(AclManager::class, $this->aclManager)
            ->bindInstance(Acl::class, $this->acl);

        if ($user->isPortal()) {
            $binder->bindInstance(PortalAcl::class, $this->acl);
            $binder->bindInstance(PortalAclManager::class, $this->aclManager);
        }

        $binder
            ->for($className)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }

    /**
     * @return class-string<FilterResolver>
     */
    private function getClassName(string $entityType): string
    {
        /** @var class-string<FilterResolver> */
        return $this->metadata->get(['selectDefs', $entityType, 'accessControlFilterResolverClassName']) ??
            DefaultFilterResolver::class;
    }

    /**
     * @return class-string<FilterResolver>
     */
    private function getPortalClassName(string $entityType): string
    {
        /** @var class-string<FilterResolver> */
        return $this->metadata->get(['selectDefs', $entityType, 'portalAccessControlFilterResolverClassName']) ??
            DefaultPortalFilterResolver::class;
    }
}
