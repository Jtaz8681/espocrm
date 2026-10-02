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

use Espo\Core\Record\CreateParams;
use Espo\Core\Record\ServiceContainer;

class CreateTest extends \tests\integration\Core\BaseTestCase
{
    protected ?string $dataFile = 'Account/ChangeFields.php';

    protected ?string $userName = 'admin';
    protected ?string $password = '1';

    public function testCreate()
    {
        $service = $this->getContainer()
            ->getByClass(ServiceContainer::class)
            ->get('Account');

        $entity = $service->create((object) [
            'name' => 'Test Account',
            'emailAddress' => 'test@tester.com',
            'phoneNumber' => '+14333633333',
        ], CreateParams::create());

        $this->assertInstanceOf('Espo\\ORM\\Entity', $entity->getEntity());
        $this->assertTrue(!empty($entity->getId()));
    }
}
