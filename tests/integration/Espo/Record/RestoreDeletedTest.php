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

use Espo\Core\Record\CreateParams;
use Espo\Core\Record\ReadParams;
use Espo\Core\Record\DeleteParams;
use Espo\Core\Record\ServiceContainer;
use tests\integration\Core\BaseTestCase;

class RestoreDeletedTest extends BaseTestCase
{
    public function testDeleted(): void
    {
        $service = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->get('Account');

        $account = $service->create((object) [
            'name' => 'Test'
        ], CreateParams::create())->getEntity();

        $service->delete($account->getId(), DeleteParams::create());

        $account = $service->read($account->getId(), ReadParams::create());

        $this->assertNotNull($account);

        $this->assertTrue($account->get('deleted'));
    }

    public function testRestoreDeleted()
    {
        $service = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->get('Account');

        $account = $service->create((object) [
            'name' => 'Test'
        ], CreateParams::create())->getEntity();

        $service->delete($account->getId(), DeleteParams::create());

        $service->restoreDeleted($account->getId());

        $account = $service->read($account->getId(), ReadParams::create())->getEntity();

        $this->assertNotNull($account);

        $this->assertFalse($account->get('deleted'));
    }
}
