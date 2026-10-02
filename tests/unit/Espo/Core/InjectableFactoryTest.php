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

namespace tests\unit\Espo\Core;

use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\Binding\BindingData;
use Espo\Core\Container;
use Espo\Core\InjectableFactory;

use tests\integration\testClasses\Binding\SomeClass;
use tests\integration\testClasses\Binding\SomeImplementation;
use tests\integration\testClasses\Binding\SomeInterface;

use tests\unit\testClasses\Core\Binding\SomeClass0;
use tests\unit\testClasses\Core\Binding\SomeClass1;
use tests\unit\testClasses\Core\Binding\SomeClass2;
use tests\unit\testClasses\Core\Binding\SomeInterface1;
use tests\unit\testClasses\Core\Binding\SomeInterface2;

class InjectableFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateWithBinding1(): void
    {
        $container = $this->createMock(Container::class);

        $injectableFactory = new InjectableFactory($container);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $instance = $this->createMock(SomeInterface::class);

        $binder->bindInstance(SomeInterface::class, $instance);

        $obj = $injectableFactory->createWithBinding(SomeClass::class, new BindingContainer($bindingData));

        $this->assertNotNull($obj);

        $this->assertSame($instance, $obj->get());
    }

    public function testCreateWithBinding2(): void
    {
        $container = $this->createMock(Container::class);

        $injectableFactory = new InjectableFactory($container);

        $bindingData = new BindingData();

        $binder = new Binder($bindingData);

        $binder
            ->bindImplementation(SomeInterface1::class, SomeClass1::class)
            ->bindImplementation(SomeInterface2::class, SomeClass2::class);

        $obj = $injectableFactory->createWithBinding(SomeClass0::class, new BindingContainer($bindingData));

        $this->assertNotNull($obj);
    }

    public function testCreateResolved1(): void
    {
        $container = $this->createMock(Container::class);

        $bindingContainer = BindingContainerBuilder::create()
            ->bindImplementation(SomeInterface::class, SomeImplementation::class)
            ->build();

        $injectableFactory = new InjectableFactory($container, $bindingContainer);

        $obj = $injectableFactory->createResolved(SomeInterface::class);

        $this->assertInstanceOf(SomeImplementation::class, $obj);
    }

    public function testCreateResolved2(): void
    {
        $container = $this->createMock(Container::class);

        $bindingContainer = BindingContainerBuilder::create()->build();

        $injectableFactory = new InjectableFactory($container, $bindingContainer);

        $bindingContainer1 = BindingContainerBuilder::create()->build();

        $obj = $injectableFactory->createResolved(SomeImplementation::class, $bindingContainer1);

        $this->assertInstanceOf(SomeImplementation::class, $obj);
    }
}
