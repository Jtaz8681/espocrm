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

namespace tests\unit\Espo\Core\Job;

use Espo\Core\{
    Job\QueueProcessor\Params,
};

class QueueProcessorParamsTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
    }

    public function testParams1()
    {
        $params = \Espo\Core\Job\QueueProcessor\Params
            ::create()
            ->withLimit(10);

        $this->assertFalse($params->useProcessPool());
        $this->assertFalse($params->noLock());

        $this->assertEquals(10, $params->getLimit());

        $this->assertNull($params->getQueue());
    }

    public function testParams2()
    {
        $params = \Espo\Core\Job\QueueProcessor\Params
            ::create()
            ->withLimit(10)
            ->withUseProcessPool(true)
            ->withNoLock(true)
            ->withGroup('group-0')
            ->withQueue('q0');

        $this->assertTrue($params->useProcessPool());
        $this->assertTrue($params->noLock());

        $this->assertEquals('q0', $params->getQueue());

        $this->assertEquals('group-0', $params->getGroup());
    }
}
