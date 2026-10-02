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

use Espo\Core\{
    Acl\AccessChecker\ScopeChecker,
    Acl\AccessChecker\ScopeCheckerData,
    Acl\ScopeData,
    Acl\Table,
};

class ScopeCheckerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopeChecker
     */
    private $scopeChecker;

    protected function setUp() : void
    {
        $this->scopeChecker = new ScopeChecker();
    }

    public function testCheckerNoData1()
    {
        $data = ScopeData::fromRaw(false);

        $result = $this->scopeChecker->check($data);

        $this->assertEquals(false, $result);
    }

    public function testCheckerNoData2()
    {
        $data = ScopeData::fromRaw(true);

        $result = $this->scopeChecker->check($data);

        $this->assertEquals(true, $result);
    }

    public function testCheckerNoData3()
    {
        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_NO,
            ],
        );

        $result = $this->scopeChecker->check($data);

        $this->assertEquals(true, $result);
    }

    public function testCheckerNoData4()
    {
        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_ALL,
            ],
        );

        $result = $this->scopeChecker->check($data);

        $this->assertEquals(true, $result);
    }

    public function testCheckerNoData5()
    {
        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_TEAM,
            ],
        );

        $result = $this->scopeChecker->check($data);

        $this->assertEquals(true, $result);
    }

    public function testCheckerActionNoData1()
    {
        $data = ScopeData::fromRaw(false);

        $result = $this->scopeChecker->check($data, Table::ACTION_CREATE);

        $this->assertEquals(false, $result);
    }

    public function testCheckerActionNoData2()
    {
        $data = ScopeData::fromRaw(true);

        $result = $this->scopeChecker->check($data, Table::ACTION_CREATE);

        $this->assertEquals(true, $result);
    }

    public function testCheckerData1()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(false)
            ->setInTeam(false)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_ALL,
            ],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(true, $result);
    }

    public function testCheckerData2()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(false)
            ->setInTeam(false)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_TEAM,
            ],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(false, $result);
    }

    public function testCheckerData3()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(false)
            ->setInTeam(false)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_OWN,
            ],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(false, $result);
    }

    public function testCheckerData4()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(false)
            ->setInTeam(false)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_NO,
            ],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(false, $result);
    }

    public function testCheckerData5()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(false)
            ->setInTeam(true)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_OWN,
            ],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(false, $result);
    }

    public function testCheckerData6()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(false)
            ->setInTeam(true)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_TEAM,
            ],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(true, $result);
    }

    public function testCheckerData7()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(true)
            ->setInTeam(true)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_TEAM,
            ],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(true, $result);
    }

    public function testCheckerData8()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(false)
            ->setInTeam(false)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_CREATE => Table::LEVEL_YES,
            ],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_CREATE, $checkerData);

        $this->assertEquals(true, $result);
    }

    public function testCheckerData9()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(true)
            ->setInTeam(false)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_OWN,
            ],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(true, $result);
    }

    public function testCheckerData10()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(true)
            ->setInTeam(true)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_OWN,
            ],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(true, $result);
    }

    public function testCheckerData11()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(true)
            ->setInTeam(false)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_TEAM,
            ],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(true, $result);
    }

    public function testCheckerData12()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(true)
            ->setInTeam(true)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [],
        );

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(false, $result);
    }

    public function testCheckerData13()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(false)
            ->setInTeam(false)
            ->build();

        $data = ScopeData::fromRaw(false);

        $result = $this->scopeChecker->check($data, Table::ACTION_READ, $checkerData);

        $this->assertEquals(false, $result);
    }

    public function testCheckerDataNoAction1()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(true)
            ->setInTeam(true)
            ->build();

        $data = ScopeData::fromRaw(false);

        $result = $this->scopeChecker->check($data, null, $checkerData);

        $this->assertEquals(false, $result);
    }

    public function testCheckerDataNoAction2()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(false)
            ->setInTeam(false)
            ->build();

        $data = ScopeData::fromRaw(true);

        $result = $this->scopeChecker->check($data, null, $checkerData);

        $this->assertEquals(true, $result);
    }

    public function testCheckerDataNoAction3()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(true)
            ->setInTeam(true)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_READ => Table::LEVEL_TEAM,
            ],
        );

        $result = $this->scopeChecker->check($data, null, $checkerData);

        $this->assertEquals(true, $result);
    }

    public function testCheckerDataNoAction4()
    {
        $checkerData = ScopeCheckerData
            ::createBuilder()
            ->setIsOwn(true)
            ->setInTeam(true)
            ->build();

        $data = ScopeData::fromRaw(
            (object) [
                Table::ACTION_CREATE => Table::LEVEL_NO,
                Table::ACTION_READ => Table::LEVEL_NO,
            ],
        );

        $result = $this->scopeChecker->check($data, null, $checkerData);

        $this->assertEquals(true, $result);
    }
}
