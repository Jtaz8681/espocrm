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

namespace tests\integration\Espo\Account;

class ChangeFieldsTest extends \tests\integration\Core\BaseTestCase
{
    protected ?string $userName = 'admin';
    protected ?string $password = '1';

    public function testChangeName()
    {
        $entityManager = $this->getContainer()->get('entityManager');

        $account = $entityManager->getEntity('Account', '53203b942850b');

        $this->assertEquals('Espo\\Modules\\Crm\\Entities\\Account', get_class($account));
        $this->assertEquals('Besharp', $account->get('name'));

        $account->set('name', 'Changed Name');
        $entityManager->saveEntity($account);

        $account = $entityManager->getEntity('Account', '53203b942850b');

        $this->assertEquals('Changed Name', $account->get('name'));
    }
}
