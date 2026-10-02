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

namespace tests\integration\Espo\Role;

use Espo\Core\Acl\Table;
use Espo\Core\Portal\Acl\Table as TablePortal;
use Espo\Core\Record\CreateParams;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Record\UpdateParams;
use Espo\Entities\PortalRole;
use Espo\Entities\Role;
use Espo\Modules\Crm\Entities\Account;
use tests\integration\Core\BaseTestCase;

class ServiceTest extends BaseTestCase
{
    public function testCreateUpdate(): void
    {
        $service = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(Role::class);

        $data = (object) [
            Account::ENTITY_TYPE => (object) [
                Table::ACTION_CREATE => Table::LEVEL_YES,
                Table::ACTION_READ => Table::LEVEL_ALL,
                Table::ACTION_EDIT => Table::LEVEL_OWN,
                Table::ACTION_DELETE => Table::LEVEL_NO,
                Table::ACTION_STREAM => Table::LEVEL_TEAM,
            ],
            'Activities' => true,
            'ExternalAccount' => false,
        ];

        $fieldData = (object) [
            Account::ENTITY_TYPE => (object) [
                'description' => (object) [
                    Table::ACTION_READ => Table::LEVEL_YES,
                    Table::ACTION_EDIT => Table::LEVEL_NO,
                ],
            ],
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $role = $service->create((object) [
            'name' => 'Test',
            'data' => $data,
            'fieldData' => $fieldData,
        ], CreateParams::create())->getEntity();

        $this->assertEquals($data, $role->get('data'));
        $this->assertEquals($fieldData, $role->get('fieldData'));

        /** @noinspection PhpUnhandledExceptionInspection */
        $role = $service->update($role->getId(), (object) [
            'data' => (object) [],
            'fieldData' => (object) [],
        ], UpdateParams::create())->getEntity();

        $this->assertEquals((object) [], $role->get('data'));
        $this->assertEquals((object) [], $role->get('fieldData'));

        /** @noinspection PhpUnhandledExceptionInspection */
        $role = $service->update($role->getId(), (object) [
            'data' => $data,
            'fieldData' => $fieldData,
        ], UpdateParams::create())->getEntity();

        $this->assertEquals($data, $role->get('data'));
        $this->assertEquals($fieldData, $role->get('fieldData'));
    }

    public function testPortalCreateUpdate(): void
    {
        $service = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(PortalRole::class);

        $data = (object) [
            Account::ENTITY_TYPE => (object) [
                Table::ACTION_CREATE => Table::LEVEL_YES,
                Table::ACTION_READ => Table::LEVEL_ALL,
                Table::ACTION_EDIT => TablePortal::LEVEL_ACCOUNT,
                Table::ACTION_DELETE => Table::LEVEL_NO,
                Table::ACTION_STREAM => TablePortal::LEVEL_ACCOUNT,
            ],
            'Activities' => true,
            'ExternalAccount' => false,
        ];

        $fieldData = (object) [
            Account::ENTITY_TYPE => (object) [
                'description' => (object) [
                    Table::ACTION_READ => Table::LEVEL_YES,
                    Table::ACTION_EDIT => Table::LEVEL_NO,
                ],
            ],
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $role = $service->create((object) [
            'name' => 'Test',
            'data' => $data,
            'fieldData' => $fieldData,
        ], CreateParams::create())->getEntity();

        $this->assertEquals($data, $role->get('data'));
        $this->assertEquals($fieldData, $role->get('fieldData'));

        /** @noinspection PhpUnhandledExceptionInspection */
        $role = $service->update($role->getId(), (object) [
            'data' => (object) [],
            'fieldData' => (object) [],
        ], UpdateParams::create())->getEntity();

        $this->assertEquals((object) [], $role->get('data'));
        $this->assertEquals((object) [], $role->get('fieldData'));

        /** @noinspection PhpUnhandledExceptionInspection */
        $role = $service->update($role->getId(), (object) [
            'data' => $data,
            'fieldData' => $fieldData,
        ], UpdateParams::create())->getEntity();

        $this->assertEquals($data, $role->get('data'));
        $this->assertEquals($fieldData, $role->get('fieldData'));
    }
}
