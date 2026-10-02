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

namespace tests\integration\Espo\Tools\AppSecret;

use Espo\Core\Utils\Crypt;
use Espo\Entities\AppSecret;
use Espo\Tools\AppSecret\SecretProvider;
use tests\integration\Core\BaseTestCase;

class SecretProviderTest extends BaseTestCase
{
    public function testGet(): void
    {
        $em = $this->getEntityManager();

        $provider = $this->getInjectableFactory()->create(SecretProvider::class);

        $secret = $em->getRDBRepositoryByClass(AppSecret::class)->getNew();
        $crypt = $this->getInjectableFactory()->create(Crypt::class);

        $value = 'hello';

        $secret
            ->setName('test')
            ->setSecretValue($crypt->encrypt($value));

        $em->saveEntity($secret);

        $this->assertEquals(
            $value,
            $provider->get('test')
        );

        $this->assertNull($provider->get('Test'));
    }
}
