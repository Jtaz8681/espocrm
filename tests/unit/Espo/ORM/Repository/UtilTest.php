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

namespace tests\unit\Espo\ORM\Repository;

use Espo\Modules\Crm\Entities\CaseObj;
use Espo\Modules\Crm\Entities\Lead;

use PHPUnit\Framework\TestCase;
use tests\unit\testData\Entities\TestEntity;

use Espo\ORM\Repository\Util;

class UtilTest extends TestCase
{
    public function testGetRDBRepositoryByClass(): void
    {
        $this->assertEquals('Lead', Util::getEntityTypeByClass(Lead::class));
        $this->assertEquals('Case', Util::getEntityTypeByClass(CaseObj::class));
        $this->assertEquals('TestEntity', Util::getEntityTypeByClass(TestEntity::class));
    }

    public function testGetRDBRepositoryByClassException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Util::getEntityTypeByClass(\stdClass::class);
    }
}
