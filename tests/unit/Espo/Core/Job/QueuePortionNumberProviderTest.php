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

use Espo\Core\Job\QueueName;
use Espo\Core\Job\QueuePortionNumberProvider;
use Espo\Core\Utils\Config;
use PHPUnit\Framework\TestCase;

class QueuePortionNumberProviderTest extends TestCase
{
    private $config;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
    }

    public function testDefault(): void
    {
        $provider = new QueuePortionNumberProvider($this->config);

        $this->assertEquals(200, $provider->get(QueueName::Q0));
        $this->assertEquals(500, $provider->get(QueueName::Q1));
        $this->assertEquals(100, $provider->get(QueueName::E0));
        $this->assertEquals(200, $provider->get('TestDefault'));
    }

    public function testConfig(): void
    {
        $this->config
            ->method('get')
            ->willReturnMap(
                [
                    ['jobQ0MaxPortion', null, 201],
                    ['jobQ1MaxPortion', null, 501],
                    ['jobE0MaxPortion', null, 101],
                    ['jobTestDefaultMaxPortion', null, 301]
                ]
            );

        $provider = new QueuePortionNumberProvider($this->config);

        $this->assertEquals(201, $provider->get(QueueName::Q0));
        $this->assertEquals(501, $provider->get(QueueName::Q1));
        $this->assertEquals(101, $provider->get(QueueName::E0));
        $this->assertEquals(301, $provider->get('TestDefault'));
    }
}
