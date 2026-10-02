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

namespace tests\unit\Espo\Core\Mail;

use Espo\Core\Mail\SenderParams;

class SenderParamsTest extends \PHPUnit\Framework\TestCase
{
    public function testFromArray1(): void
    {
        $array = [
            'fromAddress' => 'test@test',
            'fromName' => 'name',
            'replyToAddress' => 'reply@test',
            'replyToName' => 'reply',
        ];

        $this->assertEquals($array, SenderParams::fromArray($array)->toArray());
    }

    public function testFromArray2(): void
    {
        $array = [
            'fromAddress' => 'test@test',
            'fromName' => 'name',
        ];

        $this->assertEquals($array, SenderParams::fromArray($array)->toArray());
    }

    public function testBuilding(): void
    {
        $params = SenderParams::create()
            ->withFromAddress('test@test')
            ->withFromName('name')
            ->withReplyToAddress('r@test')
            ->withReplyToName('r');

        $this->assertEquals('test@test', $params->getFromAddress());
        $this->assertEquals('name', $params->getFromName());

        $this->assertEquals('r@test', $params->getReplyToAddress());
        $this->assertEquals('r', $params->getReplyToName());
    }
}
