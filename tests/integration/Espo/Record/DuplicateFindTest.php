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

namespace tests\integration\Espo\Record;

use Espo\Core\Exceptions\ConflictSilent;
use Espo\Core\Record\CreateParams;
use tests\integration\Core\BaseTestCase;

class DuplicateFindTest extends BaseTestCase
{
    public function testAccount1()
    {
        $service = $this->getContainer()
            ->get('recordServiceContainer')
            ->get('Account');

        $data1 = (object) [
            'name' => 'test1',
            'emailAddress' => 'test@test.com',
        ];

        $service->create($data1, CreateParams::create());

        $this->expectException(ConflictSilent::class);

        $data2 = (object) [
            'name' => 'test2',
            'emailAddress' => 'test@test.com',
        ];

        $service->create($data2, CreateParams::create());
    }

    public function testAccountSkip()
    {
        $service = $this->getContainer()
            ->get('recordServiceContainer')
            ->get('Account');

        $data1 = (object) [
            'name' => 'test1',
            'emailAddress' => 'test@test.com',
        ];

        $service->create($data1, CreateParams::create());

        $data2 = (object) [
            'name' => 'test2',
            'emailAddress' => 'test@test.com',
        ];

        $service->create($data2, CreateParams::create()->withSkipDuplicateCheck());

        $this->assertTrue(true);
    }

    public function testLead1()
    {
        $service = $this->getContainer()
            ->get('recordServiceContainer')
            ->get('Lead');

        $data1 = (object) [
            'lastName' => 'test1',
            'emailAddress' => 'test@test.com',
        ];

        $service->create($data1, CreateParams::create());

        $this->expectException(ConflictSilent::class);

        $data2 = (object) [
            'lastName' => 'test2',
            'emailAddress' => 'test@test.com',
        ];

        $service->create($data2, CreateParams::create());
    }

    public function testLeadSkip()
    {
        $service = $this->getContainer()
            ->get('recordServiceContainer')
            ->get('Lead');

        $data1 = (object) [
            'lastName' => 'test1',
            'emailAddress' => 'test@test.com',
        ];

        $service->create($data1, CreateParams::create());

        $data2 = (object) [
            'lastName' => 'test2',
            'emailAddress' => 'test@test.com',
        ];

        $service->create($data2, CreateParams::create()->withSkipDuplicateCheck());

        $this->assertTrue(true);
    }
}
