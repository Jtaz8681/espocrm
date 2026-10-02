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
    FieldProcessing\Loader\Params as LoaderParams,
};

class LoaderParamsTest extends \PHPUnit\Framework\TestCase
{
    public function testOne(): void
    {
        $select = [
            'id',
            'name',
        ];

        $params = LoaderParams
            ::create()
            ->withSelect($select);

        $this->assertEquals(true, $params->hasInSelect('id'));
        $this->assertEquals(false, $params->hasInSelect('test'));

        $this->assertEquals(true, $params->hasSelect());
    }

    public function testTwo(): void
    {
        $params = LoaderParams
            ::create();


        $this->assertEquals(false, $params->hasSelect());
        $this->assertEquals(false, $params->hasInSelect('test'));
    }
}
