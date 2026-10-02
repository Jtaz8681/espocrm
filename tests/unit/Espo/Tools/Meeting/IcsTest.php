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

namespace tests\unit\Espo\Tools\Meeting;

use Espo\Modules\Crm\Business\Event\Ics;
use PHPUnit\Framework\TestCase;

class IcsTest extends TestCase
{
    public function testIcs1(): void
    {
        $ics = new Ics('//BugZyro//BugZyro Calendar//EN', [
            'method' => Ics::METHOD_REQUEST,
            'status' => Ics::STATUS_CONFIRMED,
            'startDate' => strtotime('2025-01-01 10:00:00'),
            'endDate' => strtotime('2025-01-01 11:00:00'),
            'uid' => 'test-id',
            'summary' => 'Test',
            'organizer' => ['hello@test.com', 'Hello Test'],
            'description' => 'Test.',
            'stamp' => strtotime('2025-01-01 09:00:00'),
            'attendees' => [
                ['att1@test.com', 'Att 1', 'TENTATIVE'],
                ['att2@test.com', 'Att 2'],
            ],
        ]);

        $expected =
            "BEGIN:VCALENDAR\r\n".
            "VERSION:2.0\r\n".
            "PRODID:-//BugZyro//BugZyro Calendar//EN\r\n".
            "METHOD:REQUEST\r\n".
            "BEGIN:VEVENT\r\n".
            "DTSTART:20250101T100000Z\r\n".
            "DTEND:20250101T110000Z\r\n".
            "SUMMARY:Test\r\n".
            "ORGANIZER;CN=Hello Test:MAILTO:hello@test.com\r\n".
            "DESCRIPTION:Test.\r\n".
            "UID:test-id\r\n".
            "SEQUENCE:0\r\n".
            "DTSTAMP:20250101T090000Z\r\n".
            "STATUS:CONFIRMED\r\n".
            "ATTENDEE;PARTSTAT=TENTATIVE;CN=Att 1:MAILTO:att1@test.com\r\n".
            "ATTENDEE;CN=Att 2:MAILTO:att2@test.com\r\n".
            "END:VEVENT\r\n".
            "END:VCALENDAR";

        $this->assertEquals($expected, $ics->get());
    }
}
