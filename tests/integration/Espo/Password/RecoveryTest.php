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

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Entities\Portal;
use Espo\Tools\UserSecurity\Password\Recovery\UrlValidator;
use tests\integration\Core\BaseTestCase;

class RecoveryTest extends BaseTestCase
{
    private ?string $storedSiteUrl = null;

    private string $siteUrl = 'https://my-site.com/';

    protected function setUp(): void
    {
        parent::setUp();

        $writer = $this->getInjectableFactory()->create(ConfigWriter::class);
        $writer->set('siteUrl', $this->siteUrl);
        $writer->save();

        $this->storedSiteUrl = $this->getConfig()->get('siteUrl');
    }

    protected function tearDown(): void
    {
        $writer = $this->getInjectableFactory()->create(ConfigWriter::class);
        $writer->set('siteUrl', $this->storedSiteUrl);
        $writer->save();

        $this->storedSiteUrl = null;

        parent::tearDown();
    }

    public function testUrlValidation()
    {
        $em = $this->getEntityManager();

        $em->createEntity(Portal::ENTITY_TYPE, [
            'customUrl' => 'https://my-portal.com/',
        ]);

        /** @var Portal $portal2 */
        $portal2 = $em->createEntity(Portal::ENTITY_TYPE, [
            'isDefault' => true,
        ]);

        $validator = $this->getInjectableFactory()->create(UrlValidator::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $validator->validate('https://my-site.com');

        /** @noinspection PhpUnhandledExceptionInspection */
        $validator->validate('https://my-site.com/');

        /** @noinspection PhpUnhandledExceptionInspection */
        $validator->validate('https://my-site.com#Test');

        /** @noinspection PhpUnhandledExceptionInspection */
        $validator->validate('https://my-site.com/portal');

        /** @noinspection PhpUnhandledExceptionInspection */
        $validator->validate('https://my-portal.com');

        /** @noinspection PhpUnhandledExceptionInspection */
        $validator->validate('https://my-portal.com/');

        /** @noinspection PhpUnhandledExceptionInspection */
        $validator->validate('https://my-portal.com/#Test');

        /** @noinspection PhpUnhandledExceptionInspection */
        $validator->validate('https://my-site.com/portal/' . $portal2->getId());

        $thrown = false;

        try {
            $validator->validate('https://not-my-site.com');
        }
        catch (Forbidden) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
    }
}
