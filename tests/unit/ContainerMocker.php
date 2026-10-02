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

namespace tests\unit;

use Espo\Core\Container;

use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount as AnyInvokedCountMatcher;
use PHPUnit\Framework\TestCase;

use ReflectionClass;

class ContainerMocker
{
    protected $test;

    public function __construct(TestCase $test)
    {
        $this->test = $test;
    }

    public function create(array $serviceMap) : Container
    {
        $container = (new MockBuilder($this->test, Container::class))->disableOriginalConstructor()->getMock();

        $map = $serviceMap;

        $valueMap = [];
        $hasMap = [];
        $classMap = [];

        foreach ($map as $key => $value) {
            $valueMap[] = [$key, $value];
        }

        foreach ($map as $key => $value) {
            $hasMap[] = [$key, true];
        }

        foreach ($map as $key => $value) {
            $classMap[] = [$key, new ReflectionClass($value)];
        }

        $container
            ->expects(new AnyInvokedCountMatcher)
            ->method('get')
            ->willReturnMap($valueMap);

        $container
            ->expects(new AnyInvokedCountMatcher)
            ->method('has')
            ->willReturnMap($hasMap);

        $container
            ->expects(new AnyInvokedCountMatcher)
            ->method('getClass')
            ->willReturnMap($classMap);

        return $container;
    }
}
