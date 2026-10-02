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

namespace tests\unit\Espo\Core\Select\Primary;

use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingData;
use Espo\Core\InjectableFactory;
use Espo\Core\Select\Helpers\FieldHelper;
use Espo\Core\Select\Primary\FilterFactory as PrimaryFilterFactory;
use Espo\Core\Select\Primary\Filters\Followed;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use PHPUnit\Framework\TestCase;

class FilterFactoryTest extends TestCase
{
    private $injectableFactory;
    private $metadata;
    private $user;
    private $factory;

    protected function setUp(): void
    {
        $this->injectableFactory = $this->createMock(InjectableFactory::class);
        $this->metadata = $this->createMock(Metadata::class);
        $this->user = $this->createMock(User::class);

        $this->factory = new PrimaryFilterFactory(
            $this->injectableFactory,
            $this->metadata
        );
    }

    public function testCreate1()
    {
        $this->prepareFactoryTest(null, Followed::class, 'followed');
    }

    public function testCreate2()
    {
        $this->prepareFactoryTest('SomeClass', Followed::class, 'followed');
    }

    protected function prepareFactoryTest(?string $className, string $defaultClassName, string $name)
    {
        $entityType = 'Test';

        $this->metadata
            ->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [['selectDefs', $entityType, 'primaryFilterClassNameMap', $name], null, $className],
            ]);

        $className = $className ?? $defaultClassName;

        $object = $this->createMock($defaultClassName);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder
            ->bindInstance(User::class, $this->user)
            ->for($className)
            ->bindValue('$entityType', $entityType)
            ->bindValue('$name', $name);

        $binder
            ->for(FieldHelper::class)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        $this->injectableFactory
            ->expects($this->once())
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
            ->expects($this->once())
            ->method('get')
            ->with([
                'selectDefs',
                $entityType,
                'primaryFilterClassNameMap',
                'badName',
            ])
            ->willReturn(null);

        $this->assertFalse(
            $this->factory->has($entityType, 'badName')
        );
    }
}
