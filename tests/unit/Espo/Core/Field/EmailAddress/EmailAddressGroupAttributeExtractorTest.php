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

namespace tests\unit\Espo\Core\Field\EmailAddress;

use Espo\Core\Field\EmailAddress;
use Espo\Core\Field\EmailAddress\EmailAddressGroupAttributeExtractor;
use Espo\Core\Field\EmailAddressGroup;
use PHPUnit\Framework\TestCase;

class EmailAddressGroupAttributeExtractorTest extends TestCase
{
    public function testExtract()
    {
        $group = EmailAddressGroup
            ::create([
                EmailAddress::create('ONE@test.com'),
                EmailAddress::create('two@test.com')->optedOut(),
            ]);

        $valueMap = (new EmailAddressGroupAttributeExtractor())->extract($group, 'emailAddress');

        $this->assertEquals(
            (object) [
                'emailAddress' => 'ONE@test.com',
                'emailAddressData' => [
                    (object) [
                        'emailAddress' => 'ONE@test.com',
                        'lower' => 'one@test.com',
                        'primary' => true,
                        'optOut' => false,
                        'invalid' => false,
                    ],
                    (object) [
                        'emailAddress' => 'two@test.com',
                        'lower' => 'two@test.com',
                        'primary' => false,
                        'optOut' => true,
                        'invalid' => false,
                    ],
                ]
            ],
            $valueMap
        );
    }

    public function testEmpty()
    {
        $group = EmailAddressGroup
            ::create([]);

        $valueMap = (new EmailAddressGroupAttributeExtractor())->extract($group, 'emailAddress');

        $this->assertEquals(
            (object) [
                'emailAddress' => null,
                'emailAddressData' => [],
            ],
            $valueMap
        );
    }
}
