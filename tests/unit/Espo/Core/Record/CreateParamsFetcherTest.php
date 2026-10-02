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

namespace tests\unit\Espo\Core\Record;

use Espo\Core\Record\CreateParamsFetcher;
use Espo\Core\Api\RequestWrapper;
use PHPUnit\Framework\TestCase;

class CreateParamsFetcherTest extends TestCase
{
    public function test1(): void
    {
        $request = $this->createMock(RequestWrapper::class);

        $request
            ->method('hasHeader')
            ->with('X-Skip-Duplicate-Check')
            ->willReturn(true);

        $request
            ->method('getHeader')
            ->willReturnMap(
                [
                    ['X-Skip-Duplicate-Check', 'true'],
                    ['X-Duplicate-Source-Id', null],
                ]
            );

        $params = (new CreateParamsFetcher())->fetch($request);

        $this->assertTrue($params->skipDuplicateCheck());
    }

    public function test2(): void
    {
        $request = $this->createMock(RequestWrapper::class);

        $request
            ->method('hasHeader')
            ->willReturn(true);

        $request
            ->method('getHeader')
            ->willReturnMap(
                [
                    ['X-Skip-Duplicate-Check', 'false'],
                    ['X-Duplicate-Source-Id', null],
                ]
            );

        $params = (new CreateParamsFetcher())->fetch($request);

        $this->assertFalse($params->skipDuplicateCheck());
    }

    public function test3(): void
    {
        $request = $this->createMock(RequestWrapper::class);

        $request
            ->method('hasHeader')
            ->willReturn(false);

        $params = (new CreateParamsFetcher())->fetch($request);

        $this->assertFalse($params->skipDuplicateCheck());
    }

    public function test4(): void
    {
        $request = $this->createMock(RequestWrapper::class);

        $request
            ->method('hasHeader')
            ->willReturn(true);

        $request
            ->method('getHeader')
            ->willReturnMap(
                [
                    ['X-Skip-Duplicate-Check', 'TRUE'],
                    ['X-Duplicate-Source-Id', null],
                ]
            );

        $params = (new CreateParamsFetcher())->fetch($request);

        $this->assertTrue($params->skipDuplicateCheck());
    }

    public function test5(): void
    {
        $request = $this->createMock(RequestWrapper::class);

        $request
            ->method('hasHeader')
            ->willReturn(false);

        $request
            ->method('getParsedBody')
            ->willReturn((object) [
                '_skipDuplicateCheck' => true,
            ]);

        $params = (new CreateParamsFetcher())->fetch($request);

        $this->assertTrue($params->skipDuplicateCheck());
    }
}
