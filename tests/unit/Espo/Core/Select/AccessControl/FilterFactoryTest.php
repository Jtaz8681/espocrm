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

use Espo\Core\Acl;
use Espo\Core\AclManager;
use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingData;
use Espo\Core\InjectableFactory;
use Espo\Core\Select\AccessControl\FilterFactory;
use Espo\Core\Select\AccessControl\Filters\OnlyOwn;
use Espo\Core\Select\Helpers\FieldHelper;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;

use PHPUnit\Framework\TestCase;

class FilterFactoryTest extends TestCase
{
    private $aclManager;
    private $acl;
    private $metadata;
    private $injectableFactory;
    private $factory;
    private $user;

    protected function setUp() : void
    {
        $this->injectableFactory = $this->createMock(InjectableFactory::class);
        $this->metadata = $this->createMock(Metadata::class);
        $this->user = $this->createMock(User::class);
        $this->aclManager = $this->createMock(AclManager::class);
        $this->acl = $this->createMock(Acl::class);

        $this->factory = new FilterFactory(
            $this->injectableFactory,
            $this->metadata,
            $this->aclManager,
            $this->acl,
        );
    }

    public function testCreate1()
    {
        $this->prepareFactoryTest(null, OnlyOwn::class, 'onlyOwn');
    }

    public function testCreate2()
    {
        $this->prepareFactoryTest('SomeClass', OnlyOwn::class, 'onlyOwn');
    }

    protected function prepareFactoryTest(?string $className, string $defaultClassName, string $name)
    {
        $entityType = 'Test';

        $this->metadata
            ->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [['selectDefs', $entityType, 'accessControlFilterClassNameMap', $name], null, $className],
            ]);

        $className = $className ?? $defaultClassName;

        $object = $this->createMock($defaultClassName);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder
            ->bindInstance(User::class, $this->user)
            ->bindInstance(AclManager::class, $this->aclManager)
            ->bindInstance(Acl::class, $this->acl);

        $binder
            ->for($className)
            ->bindValue('$entityType', $entityType);

        $binder
            ->for(FieldHelper::class)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        $this->injectableFactory
            ->expects($this->exactly(1))
            ->method('createWithBinding')
            ->with($className, $bindingContainer)
            ->willReturn($object);

        $resultObject = $this->factory->create(
            $entityType,
            $this->user,
            $name
        );

        $this->assertEquals($object, $resultObject);

        $this->assertTrue(
            $this->factory->has($entityType, $name)
        );

        $this->metadata
            ->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [['selectDefs', $entityType, 'accessControlFilterClassNameMap', 'badName'], null, null],
            ]);

        $this->assertFalse(
            $this->factory->has($entityType, 'badName')
        );
    }
}
