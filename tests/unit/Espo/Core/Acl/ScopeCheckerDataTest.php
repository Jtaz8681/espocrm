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

namespace tests\unit\Espo\Core\Acl;

use Espo\Core\Acl\AccessChecker\ScopeCheckerData;
use PHPUnit\Framework\TestCase;

class ScopeCheckerDataTest extends TestCase
{
    protected function setUp() : void
    {
    }

    public function testCheckerData0()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->build();

        $this->assertFalse($checkerData->isOwn());
        $this->assertFalse($checkerData->inTeam());
        $this->assertFalse($checkerData->isShared());
    }

    public function testCheckerData1()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(true)
            ->setInTeam(true)
            ->setIsShared(true)
            ->build();

        $this->assertTrue($checkerData->isOwn());
        $this->assertTrue($checkerData->inTeam());
        $this->assertTrue($checkerData->isShared());
    }

    public function testCheckerData2()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(false)
            ->setInTeamChecker(
                function (): bool {
                    return true;
                }
            )
            ->setIsSharedChecker(
                function (): bool {
                    return true;
                }
            )
            ->build();

        $this->assertFalse($checkerData->isOwn());
        $this->assertTrue($checkerData->inTeam());
        $this->assertTrue($checkerData->isShared());
    }

    public function testCheckerData3()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwnChecker(
                function (): bool {
                    return true;
                }
            )
            ->setInTeamChecker(
                function (): bool {
                    return false;
                }
            )
            ->setIsSharedChecker(
                function (): bool {
                    return false;
                }
            )
            ->build();

        $this->assertTrue($checkerData->isOwn());
        $this->assertFalse($checkerData->inTeam());
        $this->assertFalse($checkerData->isShared());
    }
}
