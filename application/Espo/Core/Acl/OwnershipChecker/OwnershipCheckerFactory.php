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

namespace Espo\Core\Acl\OwnershipChecker;

use Espo\Core\Acl\DefaultOwnershipChecker;
use Espo\Core\Acl\Exceptions\NotImplemented;
use Espo\Core\Acl\OwnershipChecker;
use Espo\Core\AclManager;
use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingData;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;

class OwnershipCheckerFactory
{
    /** @var class-string<OwnershipChecker> */
    private string $defaultClassName = DefaultOwnershipChecker::class;

    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory
    ) {}

    /**
     * Create an ownership checker.
     *
     * @throws NotImplemented
     */
    public function create(string $scope, AclManager $aclManager): OwnershipChecker
    {
        $className = $this->getClassName($scope);

        $bindingContainer = $this->createBindingContainer($className, $aclManager, $scope);

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }

    /**
     * @return class-string<OwnershipChecker>
     * @throws NotImplemented
     */
    private function getClassName(string $scope): string
    {
        /** @var ?class-string<OwnershipChecker> $className */
        $className = $this->metadata->get(['aclDefs', $scope, 'ownershipCheckerClassName']);

        if ($className) {
            return $className;
        }

        if (!$this->metadata->get(['scopes', $scope])) {
            throw new NotImplemented();
        }

        return $this->defaultClassName;
    }

    /**
     * @param class-string<OwnershipChecker> $className
     */
    private function createBindingContainer(string $className, AclManager $aclManager, string $scope): BindingContainer
    {
        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder->bindInstance(AclManager::class, $aclManager);

        $binder
            ->for($className)
            ->bindValue('$entityType', $scope);

        return new BindingContainer($bindingData);
    }
}
