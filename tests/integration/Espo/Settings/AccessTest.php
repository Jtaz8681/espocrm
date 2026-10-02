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

namespace tests\integration\Espo\Settings;

use Espo\Entities\User;
use Espo\Tools\App\SettingsService;
use tests\integration\Core\BaseTestCase;

class AccessTest extends BaseTestCase
{
    public function testGlobalAccess()
    {
        $data = $this->getInjectableFactory()
            ->create(SettingsService::class)
            ->getConfigData();

        $this->assertTrue(property_exists($data, 'cacheTimestamp'));
        $this->assertFalse(property_exists($data, 'googleMapsApiKey'));
        $this->assertFalse(property_exists($data, 'outboundEmailFromAddress'));
        $this->assertFalse(property_exists($data, 'jobPeriod'));
        $this->assertFalse(property_exists($data, 'cryptKey'));
    }

    public function testUserAccess1()
    {
        $this->createUser('tester', [
            'data' => [
                'Email' => [
                    'create' => 'yes',
                    'read' => 'team',
                    'edit' => 'team',
                    'delete' => 'no'
                ]
            ]
        ]);

        $this->authenticate('tester');

        $data = $this->getInjectableFactory()
            ->create(SettingsService::class)
            ->getConfigData();

        $this->assertTrue(property_exists($data, 'version'));
        $this->assertFalse(property_exists($data, 'outboundEmailFromAddress'));
        $this->assertFalse(property_exists($data, 'jobPeriod'));
        $this->assertFalse(property_exists($data, 'cryptKey'));
    }

    public function testUserAccess2()
    {
        $this->createUser('tester', [
            'data' => [
                'Email' => false
            ]
        ]);

        $this->authenticate('tester');

        $data = $this->getInjectableFactory()
            ->create(SettingsService::class)
            ->getConfigData();

        $this->assertFalse(property_exists($data, 'outboundEmailFromAddress'));
    }

    public function testAdminAccess()
    {
        $this->createUser([
            'userName' => 'admin-tester',
            'type' => 'admin',
        ]);

        $this->authenticate('admin-tester');

        $data = $this->getInjectableFactory()
            ->create(SettingsService::class)
            ->getConfigData();

        $this->assertTrue(property_exists($data, 'version'));
        $this->assertTrue(property_exists($data, 'outboundEmailFromAddress'));
        $this->assertTrue(property_exists($data, 'jobPeriod'));
        $this->assertFalse(property_exists($data, 'cryptKey'));
    }

    public function testReadOnly(): void
    {
        $this->createUser([
            'userName' => 'admin-tester',
            'type' => User::TYPE_ADMIN,
        ]);

        $this->authenticate('admin-tester');

        $this->getInjectableFactory()
            ->create(SettingsService::class)
            ->setConfigData((object) [
                'systemUserId' => 'test'
            ]);

        $this->assertNull($this->getConfig()->get('systemUserId'));
    }
}
