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

namespace tests\unit\Espo\Core\Upgrades;

use Espo\Core\Container;
use Espo\Core\Upgrades\ActionManager;
use Espo\Core\Upgrades\Base;
use PHPUnit\Framework\TestCase;
use tests\unit\ReflectionHelper;

use Espo\Core\Utils\File\Manager as FileManager;

class ActionManagerTest extends TestCase
{
    protected $object;
    protected $objects;
    private $reflection;

    protected $params = [
        'name' => 'Extension',
        'params' => [
            'packagePath' => 'tests/unit/testData/Upgrades/data/upload/extensions',
            'backupPath' => 'tests/unit/testData/Upgrades/data/.backup/extensions',

            'scriptNames' => [
                'before' => 'BeforeInstall',
                'after' => 'AfterInstall',
                'beforeUninstall' => 'BeforeUninstall',
                'afterUninstall' => 'AfterUninstall',
            ]
        ],
    ];

    protected function setUp(): void
    {
        $this->objects['container'] = $container = $this->createMock(Container::class);

        $fileManager = $this->createMock(FileManager::class);

        $container
            ->expects($this->any())
            ->method('getByClass')
            ->willReturnMap(
                [
                    [FileManager::class, $fileManager],
                ]
            );

        $this->object = new ActionManager(
            $this->params['name'],
            $container,
            $this->params['params']
        );

        $this->reflection = new ReflectionHelper($this->object);
    }

    protected function tearDown() : void
    {
        $this->object = NULL;
    }

    public function testGetObjectExtensionUpload()
    {
        $this->object->setAction(Base::UPLOAD);

        $class = $this->reflection->invokeMethod('getObject');
        $this->assertInstanceOf('Espo\Core\Upgrades\Actions\Extension\Upload', $class);
    }

    public function testGetObjectExtensionInstall()
    {
        $this->object->setAction(Base::INSTALL);

        $class = $this->reflection->invokeMethod('getObject');
        $this->assertInstanceOf('Espo\Core\Upgrades\Actions\Extension\Install', $class);
    }

    public function testGetObjectExtensionUninstall()
    {
        $this->object->setAction(Base::UNINSTALL);

        $class = $this->reflection->invokeMethod('getObject');
        $this->assertInstanceOf('Espo\Core\Upgrades\Actions\Extension\Uninstall', $class);
    }

    public function testGetObjectExtensionDelete()
    {
        $this->object->setAction(Base::DELETE);

        $class = $this->reflection->invokeMethod('getObject');
        $this->assertInstanceOf('Espo\Core\Upgrades\Actions\Extension\Delete', $class);
    }

    public function testGetObjectExtensionNotExists()
    {
        $this->expectException('Espo\Core\Exceptions\Error');

        $this->object->setAction('CustomClass');
        $class = $this->reflection->invokeMethod('getObject');
    }

    public function testGetObjectUpgradeUpload()
    {
        $this->reflection->setProperty('managerName', 'Upgrade');
        $this->object->setAction(Base::UPLOAD);

        $class = $this->reflection->invokeMethod('getObject');
        $this->assertInstanceOf('Espo\Core\Upgrades\Actions\Upgrade\Upload', $class);
    }

    public function testGetObjectUpgradeInstall()
    {
        $this->reflection->setProperty('managerName', 'Upgrade');
        $this->object->setAction(Base::INSTALL);

        $class = $this->reflection->invokeMethod('getObject');
        $this->assertInstanceOf('Espo\Core\Upgrades\Actions\Upgrade\Install', $class);
    }

    public function testGetObjectUpgradeUninstall()
    {
        $this->expectException('Espo\Core\Exceptions\Error');

        $this->reflection->setProperty('managerName', 'Upgrade');
        $this->object->setAction(Base::UNINSTALL);

        $class = $this->reflection->invokeMethod('getObject');
        $this->assertInstanceOf('Espo\Core\Upgrades\Actions\Upgrade\Uninstall', $class);

        $class->run([]);
    }

    public function testGetObjectUpgradeDelete()
    {
        $this->expectException('Espo\Core\Exceptions\Error');

        $this->reflection->setProperty('managerName', 'Upgrade');
        $this->object->setAction(Base::DELETE);

        $class = $this->reflection->invokeMethod('getObject');
        $this->assertInstanceOf('Espo\Core\Upgrades\Actions\Upgrade\Delete', $class);

        $class->run([]);
    }
}
