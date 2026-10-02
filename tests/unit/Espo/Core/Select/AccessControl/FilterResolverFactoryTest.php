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

namespace tests\unit\Espo\Core\Select\AccessControl;

use Espo\Core\Utils\Acl\UserAclManagerProvider;

use Espo\Core\Acl;
use Espo\Core\AclManager;
use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingData;
use Espo\Core\InjectableFactory;
use Espo\Core\Portal\Acl as PortalAcl;
use Espo\Core\Portal\AclManager as PortalAclManager;
use Espo\Core\Select\AccessControl\DefaultFilterResolver;
use Espo\Core\Select\AccessControl\DefaultPortalFilterResolver;
use Espo\Core\Select\AccessControl\FilterResolverFactory;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use PHPUnit\Framework\TestCase;

class FilterResolverFactoryTest extends TestCase
{
    private $injectableFactory;
    private $metadata;
    private $user;
    private $aclManager;
    private $acl;
    private $factory;

    protected function setUp() : void
    {
        $this->injectableFactory = $this->createMock(InjectableFactory::class);
        $this->metadata = $this->createMock(Metadata::class);
        $this->user = $this->createMock(User::class);
        $this->aclManager = $this->createMock(AclManager::class);
        $this->acl = $this->createMock(Acl::class);

        $this->factory = new FilterResolverFactory(
            $this->injectableFactory,
            $this->metadata,
            $this->aclManager,
            $this->acl,
        );
    }

    public function testCreate1()
    {
        $this->prepareFactoryTest(null);
    }

    public function testCreate2()
    {
        $this->prepareFactoryTest('SomeClass');
    }

    public function testCreatePortal()
    {
        $this->user
            ->expects($this->any())
            ->method('isPortal')
            ->willReturn(true);

        $this->prepareFactoryTest(null);
    }

    protected function prepareFactoryTest(?string $className)
    {
        $entityType = 'Test';

        if (!$this->user->isPortal()) {
            $defaultClassName = DefaultFilterResolver::class;
        } else {
            $defaultClassName = DefaultPortalFilterResolver::class;
        }

        $this->metadata
            ->expects($this->once())
            ->method('get')
            ->with([
                'selectDefs', $entityType,
                !$this->user->isPortal() ?
                    'accessControlFilterResolverClassName':
                    'portalAccessControlFilterResolverClassName'
            ])
            ->willReturn($className);

        $className = $className ?? $defaultClassName;

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder
            ->bindInstance(User::class, $this->user)
            ->bindInstance(AclManager::class, $this->aclManager)
            ->bindInstance(Acl::class, $this->acl);


        if ($this->user->isPortal()) {
            $binder->bindInstance(PortalAcl::class, $this->acl);
            $binder->bindInstance(PortalAclManager::class, $this->aclManager);
        }

        $binder
            ->for($className)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        $object = $this->createMock($defaultClassName);

        $this->injectableFactory
            ->expects($this->once())
            ->method('createWithBinding')
            ->with($className, $bindingContainer)
            ->willReturn($object);

        $resultObject = $this->factory->create($entityType, $this->user);

        $this->assertEquals($object, $resultObject);
    }
}
