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

namespace tests\integration\Espo\User;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Formula\Exceptions\NotAllowedUsage;
use Espo\Core\Formula\Manager as FormulaManager;
use Espo\Core\Record\ServiceContainer;
use Espo\Entities\User;
use tests\integration\Core\BaseTestCase;

class AccessTest extends BaseTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testTypeChange(): void
    {
        $this->createUser([
            User::FIELD_USER_NAME => 'admin-test',
            User::ATTR_TYPE => User::TYPE_ADMIN,
        ]);

        $this->authenticate('admin-test');

        $service = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->getByClass(User::class);

        //

        $userRegular = $service->create((object) [
            'userName' => 'test-regular',
            'type' => User::TYPE_REGULAR
        ])->getEntity();

        //

        $thrown = false;

        try {
            $service->create((object) [
                'userName' => 'test',
                'type' => User::TYPE_SUPER_ADMIN,
            ]);
        } catch (Forbidden) {
            $thrown = true;
        }

        $this->assertTrue($thrown);

        //

        $service->update($userRegular->getId(), (object) [
            'type' => User::TYPE_ADMIN,
        ]);

        //

        $thrown = false;

        try {
            $service->update($userRegular->getId(), (object) [
                'type' => User::TYPE_SUPER_ADMIN,
            ]);
        } catch (Forbidden) {
            $thrown = true;
        }

        $this->assertTrue($thrown);

        //

        $fm = $this->getContainer()->getByClass(FormulaManager::class);

        $script = <<<'EOT'
            $data = object\create();
            $data['type'] = 'super-admin';

            record\update('User', '{{id}}', $data);
        EOT;

        $script = strtr($script, [
            '{{id}}' => $userRegular->getId(),
        ]);

        $thrown = false;

        try {
            $fm->run($script);
        } catch (NotAllowedUsage) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
    }
}
