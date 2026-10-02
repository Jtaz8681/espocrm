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

namespace tests\unit\Espo\Core\Field\PhoneNumber;

use Espo\Core\Field\PhoneNumber;
use Espo\Core\Field\PhoneNumber\PhoneNumberGroupAttributeExtractor;
use Espo\Core\Field\PhoneNumberGroup;
use PHPUnit\Framework\TestCase;

class PhoneNumberGroupAttributeExtractorTest extends TestCase
{
    public function testExtract()
    {
        $group = PhoneNumberGroup
            ::create([
                PhoneNumber::create('+1')->withType('Test-1'),
                PhoneNumber::create('+2')->withType('Test-2')->optedOut(),
            ]);

        $valueMap = (new PhoneNumberGroupAttributeExtractor())->extract($group, 'phoneNumber');

        $this->assertEquals(
            (object) [
                'phoneNumber' => '+1',
                'phoneNumberData' => [
                    (object) [
                        'phoneNumber' => '+1',
                        'type' => 'Test-1',
                        'primary' => true,
                        'optOut' => false,
                        'invalid' => false,
                    ],
                    (object) [
                        'phoneNumber' => '+2',
                        'type' => 'Test-2',
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
        $group = PhoneNumberGroup
            ::create([]);

        $valueMap = (new PhoneNumberGroupAttributeExtractor())->extract($group, 'phoneNumber');

        $this->assertEquals(
            (object) [
                'phoneNumber' => null,
                'phoneNumberData' => [],
            ],
            $valueMap
        );
    }
}
