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

namespace tests\integration\Espo\Password;

use Espo\Entities\User;
use tests\integration\Core\BaseTestCase;

class VersionTest extends BaseTestCase
{
    public function testVersionIncrement(): void
    {
        $em = $this->getEntityManager();

        $user = $em->getRDBRepositoryByClass(User::class)->getNew();
        $user->setUserName('test');
        $em->saveEntity($user);

        $version = $user->get(User::FIELD_PASSWORD_VERSION);

        $user->set(User::FIELD_PASSWORD, '1');
        $em->saveEntity($user);

        $this->assertEquals($version + 1, $user->get(User::FIELD_PASSWORD_VERSION));
    }
}
