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

namespace tests\unit\Espo\Core\Console;

use Espo\Core\Console\Command\Params;

class ParamsTest extends \PHPUnit\Framework\TestCase
{
    public function testParams()
    {
        $raw = [
           'argumentList' => ['a1', 'a2'],
           'flagList' => ['f1', 'f2', 'f3'],
           'options' => [
               'optionOne' => 'test',
            ],
        ];

        $params = new Params(
            $raw['options'],
            $raw['flagList'],
            $raw['argumentList']
        );

        $this->assertEquals($raw['argumentList'], $params->getArgumentList());
        $this->assertEquals($raw['flagList'], $params->getFlagList());
        $this->assertEquals($raw['options'], $params->getOptions());

        $this->assertTrue($params->hasFlag('f1'));
        $this->assertFalse($params->hasFlag('f0'));

        $this->assertTrue($params->hasOption('optionOne'));
        $this->assertFalse($params->hasOption('optionZero'));

        $this->assertEquals('test', $params->getOption('optionOne'));
        $this->assertEquals(null, $params->getOption('optionTwo'));

        $this->assertEquals('a1', $params->getArgument(0));
        $this->assertEquals(null, $params->getArgument(4));
    }
}
