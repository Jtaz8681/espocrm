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

namespace tests\integration\Espo\Tools\Notification;

use Espo\Core\Select\SearchParams;
use Espo\Entities\User;
use Espo\Tools\Notification\RecordService;
use tests\integration\Core\BaseTestCase;

class RecordServiceTest extends BaseTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testGet(): void
    {
        $this->createUser('tester');
        $this->authenticate('tester');

        $service = $this->getInjectableFactory()->create(RecordService::class);

        $user = $this->getContainer()->getByClass(User::class);

        $service->get($user, SearchParams::create());

        $this->assertTrue(true);
    }
}
