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

namespace tests\unit\Espo\Core\Mail\Account;

use Espo\Core\Mail\Account\FetchData;

use Espo\Core\Field\DateTime;

class FetchDataTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSet(): void
    {
        $raw = (object) [
            'lastUID' => (object) [
                'test' => '10',
            ],
            'lastDate' => (object) [
                'test' => '2022-01-01 00:00:00',
            ],
        ];

        $data = FetchData::fromRaw($raw, 0);

        $this->assertEquals(10, $data->getLastUid('test'));
        $this->assertEquals('2022-01-01 00:00:00', $data->getLastDate('test')->toString());
        $this->assertEquals(null, $data->getLastUid('not-existing'));
        $this->assertEquals(null, $data->getLastDate('not-existing'));
        $this->assertEquals(false, $data->getForceByDate('test'));
        $this->assertEquals($raw, $data->getRaw());

        $now = DateTime::createNow();

        $data->setForceByDate('test', true);
        $data->setLastUid('test', 11);
        $data->setLastDate('test', $now);

        $this->assertEquals(11, $data->getLastUid('test'));
        $this->assertEquals(true, $data->getForceByDate('test'));
        $this->assertEquals($now, $data->getLastDate('test'));
    }
}
