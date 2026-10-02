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

namespace tests\unit\Espo\Core\Portal\Acl;

use Espo\Core\{
    Portal\Acl\AccessChecker\ScopeCheckerData,

};

class ScopeCheckerDataTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
    }

    public function testCheckerData0()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->build();

        $this->assertEquals(false, $checkerData->isOwn());
        $this->assertEquals(false, $checkerData->inAccount());
        $this->assertEquals(false, $checkerData->inContact());
    }

    public function testCheckerData1()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(true)
            ->setInAccount(true)
            ->setInContact(true)
            ->build();

        $this->assertEquals(true, $checkerData->isOwn());
        $this->assertEquals(true, $checkerData->inAccount());
        $this->assertEquals(true, $checkerData->inContact());
    }

    public function testCheckerData2()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(false)
            ->setInAccountChecker(
                function (): bool {
                    return true;
                }
            )
            ->build();

        $this->assertEquals(false, $checkerData->isOwn());
        $this->assertEquals(true, $checkerData->inAccount());
    }

    public function testCheckerData3()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwnChecker(
                function (): bool {
                    return false;
                }
            )
            ->setInAccountChecker(
                function (): bool {
                    return false;
                }
            )
            ->setInContactChecker(
                function (): bool {
                    return true;
                }
            )
            ->build();

        $this->assertEquals(false, $checkerData->isOwn());
        $this->assertEquals(false, $checkerData->inAccount());
        $this->assertEquals(true, $checkerData->inContact());
    }
}
