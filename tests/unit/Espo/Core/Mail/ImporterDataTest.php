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

use Espo\Core\Mail\Importer\Data as ImporterData;

use Espo\Entities\EmailFilter;

class ImporterDataTest extends \PHPUnit\Framework\TestCase
{

    function testData1()
    {
        $filter = $this->createMock(EmailFilter::class);

        $data = ImporterData
            ::create()
            ->withTeamIdList(['t1'])
            ->withUserIdList(['u1'])
            ->withAssignedUserId('a1')
            ->withFetchOnlyHeader(true)
            ->withFolderData(['t' => '1'])
            ->withFilterList([$filter]);

        $this->assertEquals(['t1'], $data->getTeamIdList());
        $this->assertEquals(['u1'], $data->getUserIdList());
        $this->assertEquals('a1', $data->getAssignedUserId());
        $this->assertEquals(true, $data->fetchOnlyHeader());
        $this->assertEquals(['t' => '1'], $data->getFolderData());
        $this->assertEquals([$filter], $data->getFilterList());
    }

    function testData2()
    {

        $data = ImporterData
            ::create()
            ->withFetchOnlyHeader(false);

        $this->assertEquals([], $data->getTeamIdList());
        $this->assertEquals([], $data->getUserIdList());
        $this->assertEquals(null, $data->getAssignedUserId());
        $this->assertEquals(false, $data->fetchOnlyHeader());
        $this->assertEquals([], $data->getFilterList());
    }
}
