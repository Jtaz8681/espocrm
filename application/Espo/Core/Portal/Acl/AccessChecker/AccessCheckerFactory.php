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

namespace Espo\Core\Portal\Acl\AccessChecker;

use Espo\Core\Acl\AccessChecker;
use Espo\Core\Acl\Exceptions\NotImplemented;
use Espo\Core\AclManager;
use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingData;
use Espo\Core\InjectableFactory;
use Espo\Core\Portal\Acl\DefaultAccessChecker;
use Espo\Core\Portal\AclManager as PortalAclManager;
use Espo\Core\Utils\Metadata;

class AccessCheckerFactory
{
    /** @var class-string<AccessChecker> */
    private $defaultClassName = DefaultAccessChecker::class;

    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory
    ) {}

    /**
     * Create an access checker.
     *
     * @throws NotImplemented
     */
    public function create(string $scope, PortalAclManager $aclManager): AccessChecker
    {
        $className = $this->getClassName($scope);

        $bindingContainer = $this->createBindingContainer($className, $aclManager, $scope);

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }

    /**
     * @return class-string<AccessChecker>
     */
    private function getClassName(string $scope): string
    {
        /** @var ?class-string<AccessChecker> $className1 */
        $className1 = $this->metadata->get(['aclDefs', $scope, 'portalAccessCheckerClassName']);

        if ($className1) {
            return $className1;
        }

        if (!$this->metadata->get(['scopes', $scope])) {
            throw new NotImplemented();
        }

        return $this->defaultClassName;
    }

    /**
     * @param class-string<AccessChecker> $className
     */
    private function createBindingContainer(
        string $className,
        PortalAclManager $aclManager,
        string $scope
    ): BindingContainer {

        $bindingData = new BindingData();
        $binder = new Binder($bindingData);

        $binder
            ->bindInstance(PortalAclManager::class, $aclManager)
            ->bindInstance(AclManager::class, $aclManager);

        $binder
            ->for($className)
            ->bindValue('$entityType', $scope);

        return new BindingContainer($bindingData);
    }
}
