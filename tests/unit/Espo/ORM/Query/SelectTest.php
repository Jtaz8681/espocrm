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

namespace tests\unit\Espo\ORM\Query;

use Espo\ORM\Query\SelectBuilder;

class SelectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SelectBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new SelectBuilder();
    }

    public function testGetWhere1(): void
    {
        $query = $this->builder
            ->from('Test')
            ->where([
                'test' => 'hello'
            ])
            ->build();

        $this->assertEquals(
            [
                'test' => 'hello'
            ],
            $query->getWhere()->getRaw()
        );
    }

    public function testGetWhere2(): void
    {
        $query = $this->builder
            ->from('Test')
            ->build();

        $this->assertNull(
            $query->getWhere()
        );
    }
}
