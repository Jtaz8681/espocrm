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

namespace tests\integration\Espo\Core\Binding;

use Espo\Core\Application\ApplicationParams;
use Espo\Core\Binding\Binding;
use Espo\Core\Binding\BindingData;
use Espo\Core\Binding\BindingLoader;
use Espo\Core\Container\ContainerBuilder;

use tests\integration\Core\BaseTestCase;
use tests\integration\testClasses\Binding\SomeClass;
use tests\integration\testClasses\Binding\SomeClassRequiringService;
use tests\integration\testClasses\Binding\SomeClassRequiringValue;
use tests\integration\testClasses\Binding\SomeFactory;
use tests\integration\testClasses\Binding\SomeImplementation;
use tests\integration\testClasses\Binding\SomeInterface;
use tests\integration\testClasses\Binding\SomeService;

class BindingTest extends BaseTestCase
{
    public function testImplementation(): void
    {
        $bindingLoader = new class() implements BindingLoader
        {
            public function load(): BindingData
            {
                $data = new BindingData();

                $data->addGlobal(
                    SomeInterface::class,
                    Binding::createFromImplementationClassName(
                        SomeImplementation::class
                    )
                );

                return $data;
            }
        };

        $container = (new ContainerBuilder())
            ->withBindingLoader($bindingLoader)
            ->withParams(new ApplicationParams(noErrorHandler: true))
            ->build();

        $injectableFactory = $container->get('injectableFactory');

        $obj = $injectableFactory->create(SomeClass::class);

        $this->assertNotNull($obj);

        $this->assertInstanceOf(
            SomeImplementation::class,
            $obj->get()
        );
    }

    public function testFactory(): void
    {
        $bindingLoader = new class() implements BindingLoader
        {
            public function load(): BindingData
            {
                $data = new BindingData();

                $data->addGlobal(
                    SomeInterface::class,
                    Binding::createFromFactoryClassName(
                        SomeFactory::class
                    )
                );

                return $data;
            }
        };

        $container = (new ContainerBuilder())
            ->withBindingLoader($bindingLoader)
            ->withParams(new ApplicationParams(noErrorHandler: true))
            ->build();

        $injectableFactory = $container->get('injectableFactory');

        $obj = $injectableFactory->create(SomeClass::class);

        $this->assertNotNull($obj);

        $this->assertInstanceOf(
            SomeImplementation::class,
            $obj->get()
        );
    }

    public function testCallback()
    {
        $bindingLoader = new class() implements BindingLoader
        {
            public function load() : BindingData
            {
                $data = new BindingData();

                $data->addGlobal(
                    SomeInterface::class,
                    Binding::createFromCallback(
                        function (SomeImplementation $some) {
                            return $some;
                        }
                    )
                );

                return $data;
            }
        };

        $container = (new ContainerBuilder())
            ->withBindingLoader($bindingLoader)
            ->withParams(new ApplicationParams(noErrorHandler: true))
            ->build();

        $injectableFactory = $container->get('injectableFactory');

        $obj = $injectableFactory->create(SomeClass::class);

        $this->assertNotNull($obj);

        $this->assertInstanceOf(
            SomeImplementation::class,
            $obj->get()
        );
    }

    public function testService()
    {
        $bindingLoader = new class() implements BindingLoader
        {
            public function load() : BindingData
            {
                $data = new BindingData();

                $data->addGlobal(
                    SomeService::class,
                    Binding::createFromServiceName('someService')
                );

                return $data;
            }
        };

        $someService = new SomeService();

        $container = (new ContainerBuilder())
            ->withServices([
                'someService' => $someService,
            ])
            ->withBindingLoader($bindingLoader)
            ->withParams(new ApplicationParams(noErrorHandler: true))
            ->build();

        $injectableFactory = $container->get('injectableFactory');

        $obj = $injectableFactory->create(SomeClassRequiringService::class);

        $this->assertNotNull($obj);

        $this->assertSame(
            $someService,
            $obj->getService()
        );
    }

    public function testValue()
    {
        $bindingLoader = new class() implements BindingLoader
        {
            public function load() : BindingData
            {
                $data = new BindingData();

                $data->addContext(
                    SomeClassRequiringValue::class,
                    '$value',
                    Binding::createFromValue('TEST_VALUE')
                );

                return $data;
            }
        };

        $container = (new ContainerBuilder())
            ->withBindingLoader($bindingLoader)
            ->withParams(new ApplicationParams(noErrorHandler: true))
            ->build();

        $injectableFactory = $container->get('injectableFactory');

        $obj = $injectableFactory->create(SomeClassRequiringValue::class);

        $this->assertNotNull($obj);

        $this->assertSame(
            'TEST_VALUE',
            $obj->getValue()
        );
    }
}
