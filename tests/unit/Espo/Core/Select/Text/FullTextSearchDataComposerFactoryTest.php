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

namespace tests\unit\Espo\Core\Select\Text;

use Espo\Core\InjectableFactory;
use Espo\Core\Select\Text\FullTextSearch\DataComposerFactory as FullTextSearchDataComposerFactory;
use Espo\Core\Select\Text\FullTextSearch\DefaultDataComposer as FullTextSearchDataComposer;
use Espo\Core\Utils\Metadata;
use PHPUnit\Framework\TestCase;

class FullTextSearchDataComposerFactoryTest extends TestCase
{
    private $injectableFactory;
    private $metadata;
    private $factory;

    protected function setUp() : void
    {
        $this->injectableFactory = $this->createMock(InjectableFactory::class);
        $this->metadata = $this->createMock(Metadata::class);

        $this->factory = new FullTextSearchDataComposerFactory(
            $this->injectableFactory,
            $this->metadata
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

    protected function prepareFactoryTest(?string $className)
    {
        $entityType = 'Test';

        $defaultClassName = FullTextSearchDataComposer::class;

        $this->metadata
            ->expects($this->once())
            ->method('get')
            ->with([
                'selectDefs', $entityType, 'fullTextSearchDataComposerClassName'
            ])
            ->willReturn($className);

        $className = $className ?? $defaultClassName;

        $object = $this->createMock($defaultClassName);

        $this->injectableFactory
            ->expects($this->once())
            ->method('createWith')
            ->with(
                $className,
                [
                    'entityType' => $entityType,
                ]
            )
            ->willReturn($object);

        $resultObject = $this->factory->create($entityType);

        $this->assertEquals($object, $resultObject);
    }
}
