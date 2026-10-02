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

namespace tests\unit\Espo\Core\Api;

use Espo\Core\{
    Utils\ClassFinder,
    InjectableFactory,
    Api\ControllerActionProcessor,
    Api\RequestWrapper,
    Api\ResponseWrapper,
};

use tests\unit\testClasses\Controllers\TestController;

class ActionProcessor extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->classFinder = $this->createMock(ClassFinder::class);
        $this->injectableFactory = $this->createMock(InjectableFactory::class);
        $this->request = $this->createMock(RequestWrapper::class);
        $this->response = $this->createMock(ResponseWrapper::class);

        $this->actionProcessor = new ActionProcessor($this->injectableFactory, $this->classFinder);
    }

    public function testAction1()
    {
        $controller = $this->getMockBuilder(TestController::class)->disableOriginalConstructor()->getMock();

        $this->classFinder
            ->expects($this->once())
            ->method('find')
            ->with('Controllers', 'Test')
            ->willReturn(TestController::class);

        $this->request
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('POST');

        $this->injectableFactory
            ->expects($this->once())
            ->method('createWith')
            ->with(TestController::class, ['name' => 'Test'])
            ->willReturn($controller);

        $controller
            ->expects($this->once())
            ->method('postActionHello')
            ->with($this->request, $this->response);

        $this->actionProcessor->process('Test', 'hello', $this->request, $this->response);
    }
}
