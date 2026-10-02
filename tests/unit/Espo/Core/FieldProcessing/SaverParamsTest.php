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

namespace tests\unit\Espo\Core\FieldProcessing;

use Espo\Core\{
    FieldProcessing\Saver\Params,
    ORM\Repository\Option\SaveOption};

class SaverParamsTest extends \PHPUnit\Framework\TestCase
{
    public function testOne(): void
    {
        $options = [
            SaveOption::SILENT => true,
        ];

        $params = Params
            ::create()
            ->withRawOptions($options);

        $this->assertEquals(true, $params->getOption('silent'));
        $this->assertEquals(true, $params->hasOption('silent'));
        $this->assertEquals(null, $params->getOption('skipHooks'));
        $this->assertEquals(false, $params->hasOption('skipHooks'));

        $this->assertEquals($options, $params->getRawOptions());
    }
}
