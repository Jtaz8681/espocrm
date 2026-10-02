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

namespace tests\unit\Espo\Core\Select\Where;

use Espo\Core\{
    Select\Where\Params,
};

use InvalidArgumentException;

class ParamsTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
    }

    public function testFromArray()
    {
        $item = Params::fromAssoc([
            'applyPermissionCheck' => true,
            'forbidComplexExpressions' => true,
        ]);

        $this->assertTrue($item->applyPermissionCheck());
        $this->assertTrue($item->forbidComplexExpressions());

        $item = Params::fromAssoc([
            'applyPermissionCheck' => false,
            'forbidComplexExpressions' => false,
        ]);

        $this->assertFalse($item->applyPermissionCheck());
        $this->assertFalse($item->forbidComplexExpressions());

        $item = Params::fromAssoc([
            'applyPermissionCheck' => false,
            'forbidComplexExpressions' => true,
        ]);

        $this->assertFalse($item->applyPermissionCheck());
        $this->assertTrue($item->forbidComplexExpressions());
    }

    public function testEmpty()
    {
        $item = Params::fromAssoc([
        ]);

        $this->assertFalse($item->applyPermissionCheck());
        $this->assertFalse($item->forbidComplexExpressions());
    }

    public function testNonExistingParam()
    {
        $this->expectException(InvalidArgumentException::class);

        $params = Params::fromAssoc([
            'bad' => 'd',
        ]);
    }
}
