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

namespace tests\unit\Espo\Core\Select\Where;

use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingData;
use Espo\Core\InjectableFactory;
use Espo\Core\Select\Where\ItemConverterFactory;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use PHPUnit\Framework\TestCase;
use tests\unit\testClasses\Core\Select\Where\ItemConverters\TestConverter;

class ItemConverterFactoryTest extends TestCase
{
    private $metadata;
    private $injectableFactory;
    private $user;
    private $factory;

    protected function setUp(): void
    {
        $this->injectableFactory = $this->createMock(InjectableFactory::class);
        $this->metadata = $this->createMock(Metadata::class);
        $this->user = $this->createMock(User::class);

        $this->factory = new ItemConverterFactory(
            $this->injectableFactory,
            $this->metadata
        );
    }

    public function testCreateForType()
    {
        $this->prepareFactoryTest(TestConverter::class);
        $this->prepareFactoryTest(TestConverter::class, true);
    }

    public function testHasFalseForType()
    {
        $this->metadata
            ->expects($this->once())
            ->method('get')
            ->with([
                'app', 'select', 'whereItemConverterClassNameMap', 'someType'
            ])
            ->willReturn(null);

        $this->assertFalse(
            $this->factory->hasForType('someType')
        );
    }

    public function testCreateEntityType()
    {
        $this->prepareFactoryTestEntityType(TestConverter::class);
        $this->prepareFactoryTestEntityType(TestConverter::class, true);
    }

    protected function prepareFactoryTest(?string $className, bool $testHas = false)
    {
        $entityType = 'Test';

        $type = 'someType';

        $this->metadata
            ->expects($this->any())
            ->method('get')
            ->with([
                'app', 'select', 'whereItemConverterClassNameMap', $type
            ])
            ->willReturn($className);

        if ($testHas) {
            $this->assertTrue(
                $this->factory->hasForType($type)
            );

            return;
        }

        $object = $this->createMock($className);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder
            ->bindInstance(User::class, $this->user);

        $binder
            ->for($className)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        $this->injectableFactory
            ->expects($this->once())
            ->method('createWithBinding')
            ->with(
                $className,
                $bindingContainer
            )
            ->willReturn($object);

        $resultObject = $this->factory->createForType(
            $type,
            $entityType,
            $this->user
        );

        $this->assertEquals($object, $resultObject);
    }

    protected function prepareFactoryTestEntityType(?string $className, bool $testHas = false)
    {
        $entityType = 'Test';

        $type = 'someType';

        $attribute = 'test';

        $this->metadata
            ->expects($this->any())
            ->method('get')
            ->with([
                'selectDefs', $entityType, 'whereItemConverterClassNameMap', $attribute . '_' . $type
            ])
            ->willReturn($className);

        if ($testHas) {
            $this->assertTrue(
                $this->factory->has(
                    $entityType,
                    $attribute,
                    $type
                )
            );

            return;
        }

        $object = $this->createMock($className);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder
            ->bindInstance(User::class, $this->user);

        $binder
            ->for($className)
            ->bindValue('$entityType', $entityType);

        $bindingContainer = new BindingContainer($bindingData);

        $this->injectableFactory
            ->expects($this->once())
            ->method('createWithBinding')
            ->with(
                $className,
                $bindingContainer
            )
            ->willReturn($object);

        $resultObject = $this->factory->create(
            $entityType,
            $attribute,
            $type,
            $this->user
        );

        $this->assertEquals($object, $resultObject);
    }
}
