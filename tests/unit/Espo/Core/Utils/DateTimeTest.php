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

namespace tests\unit\Espo\Core\Utils;

use Espo\Core\Utils\DateTime;

class DateTimeTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        date_default_timezone_set('UTC');
    }

    public function testConvertFormat(): void
    {
        $map = [
            'YYYY-MM-DD' => 'Y-m-d',
            'DD-MM-YYYY' => 'd-m-Y',
            'MM-DD-YYYY' => 'm-d-Y',
            'MM/DD/YYYY' => 'm/d/Y',
            'DD/MM/YYYY' => 'd/m/Y',
            'DD.MM.YYYY' => 'd.m.Y',
            'DD. MM. YYYY' => 'd. m. Y',
            'MM.DD.YYYY' => 'm.d.Y',
            'YYYY.MM.DD' => 'Y.m.d',
            'HH:mm' => 'H:i',
            'HH:mm:ss' => 'H:i:s',
            'hh:mm a' => 'h:i a',
            'hh:mma' => 'h:ia',
            'hh:mm A' => 'h:i A',
            'hh:mmA' => 'h:iA',
            'DD. MM. YYYY HH:mm' => 'd. m. Y H:i',
        ];

        foreach ($map as $from => $to) {
            $this->assertEquals($to, DateTime::convertFormatToSystem($from));
        }
    }

    public function testConvertGetFormat(): void
    {
        $util = new DateTime('YYYY-MM-DD', 'HH:mm', 'Europe/Kyiv');

        $this->assertEquals('YYYY-MM-DD HH:mm', $util->getDateTimeFormat());

        $this->assertEquals('YYYY-MM-DD', $util->getDateFormat());
    }

    public function testConvertSystemDateTime1(): void
    {
        $util = new DateTime('DD-MM-YYYY', 'HH:mm', 'Europe/Kyiv');

        $this->assertEquals(
            '20-05-2021 13:00',
            $util->convertSystemDateTime('2021-05-20 10:00')
        );
    }

    public function testConvertSystemDateTime2(): void
    {
        $util = new DateTime('DD-MM-YYYY', 'HH:mm', 'Europe/Kyiv');

        $this->assertEquals(
            '2021-05-20 10:00am',
            $util->convertSystemDateTime('2021-05-20 10:00', 'UTC', 'YYYY-MM-DD hh:mma')
        );
    }

    public function testConvertSystemDate1(): void
    {
        $util = new DateTime('DD-MM-YYYY', 'HH:mm', 'Europe/Kyiv');

        $this->assertEquals(
            '20-05-2021',
            $util->convertSystemDate('2021-05-20')
        );
    }
}
