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

namespace tests\unit\Espo\Core\ORM;

use Espo\Core\ORM\DatabaseParamsFactory;
use Espo\Core\Utils\Config;

class DatabaseParamsFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate(): void
    {
        $factory = new DatabaseParamsFactory($this->createConfig());

        $params = $factory->create();

        $this->assertEquals('test:host', $params->getHost());
        $this->assertEquals(10, $params->getPort());
        $this->assertEquals('name-db', $params->getName());
        $this->assertEquals('test-user', $params->getUsername());
        $this->assertEquals('test-password', $params->getPassword());
        $this->assertEquals('test-platform', $params->getPlatform());
    }

    public function testCreateWithMergedAssoc(): void
    {
        $factory = new DatabaseParamsFactory($this->createConfig());

        $params = $factory->createWithMergedAssoc([
            'host' => 'test:host2',
            'port' => 11,
            'dbname' => 'name2-db',
            'user' => 'test-user2',
            'password' => 'test-password2',
        ]);

        $this->assertEquals('test:host2', $params->getHost());
        $this->assertEquals(11, $params->getPort());
        $this->assertEquals('name2-db', $params->getName());
        $this->assertEquals('test-user2', $params->getUsername());
        $this->assertEquals('test-password2', $params->getPassword());
        $this->assertEquals('test-platform', $params->getPlatform());
    }

    private function createConfig(): Config
    {
        $config = $this->createMock(Config::class);

        $config->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['database', null, ['d' => 'd']],
                ['database.host', null, 'test:host'],
                ['database.port', null, 10],
                ['database.dbname', null, 'name-db'],
                ['database.user', null, 'test-user'],
                ['database.password', null, 'test-password'],
                ['database.platform', null, 'test-platform'],
            ]);

        return $config;
    }
}
