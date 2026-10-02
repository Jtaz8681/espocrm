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

namespace tests\integration\Espo\Event;

use Espo\Core\Field\DateTime;
use Espo\Core\Field\DateTimeOptional;
use Espo\Modules\Crm\Entities\Call;
use Espo\Modules\Crm\Entities\Meeting;
use tests\integration\Core\BaseTestCase;

class EventTest extends BaseTestCase
{
    public function testMeetingAutomaticDateEnd(): void
    {
        $em = $this->getEntityManager();

        $meeting = $em->getRDBRepositoryByClass(Meeting::class)->getNew();
        $meeting->setDateStart(DateTimeOptional::fromString('2030-01-01 10:00'));
        $meeting->setDuration(60);
        $em->saveEntity($meeting);
        $em->refreshEntity($meeting);

        $this->assertEquals('2030-01-01 10:01:00', $meeting->getDateEnd()?->toString());

        //

        $meeting = $em->getRDBRepositoryByClass(Meeting::class)->getNew();
        $meeting->setDateStart(DateTimeOptional::fromString('2030-01-01 10:00'));
        $meeting->setDateEnd(DateTimeOptional::fromString('2030-01-01 11:00'));
        $em->saveEntity($meeting);
        $em->refreshEntity($meeting);

        $this->assertEquals('2030-01-01 11:00:00', $meeting->getDateEnd()?->toString());

        //

        $meeting = $em->getRDBRepositoryByClass(Meeting::class)->getNew();
        $meeting->setIsAllDay(true);
        $meeting->setDateStart(DateTimeOptional::fromString('2030-01-01'));
        $meeting->setDuration(3600 * 24 * 2);
        $em->saveEntity($meeting);
        $em->refreshEntity($meeting);

        $this->assertEquals('2030-01-02', $meeting->getDateEnd()?->toString());

        //

        $meeting = $em->getRDBRepositoryByClass(Meeting::class)->getNew();
        $meeting->setIsAllDay(true);
        $meeting->setDateStart(DateTimeOptional::fromString('2030-01-01'));
        $meeting->setDateEnd(DateTimeOptional::fromString('2030-01-02'));
        $em->saveEntity($meeting);
        $em->refreshEntity($meeting);

        $this->assertEquals('2030-01-02', $meeting->getDateEnd()?->toString());
    }

    public function testCallAutomaticDateEnd(): void
    {
        $em = $this->getEntityManager();

        $call = $em->getRDBRepositoryByClass(Call::class)->getNew();

        $call->setDateStart(DateTime::fromString('2030-01-01 10:00'));
        $call->setDuration(60);

        $em->saveEntity($call);

        $em->refreshEntity($call);

        $this->assertEquals('2030-01-01 10:01:00', $call->getDateEnd()?->toString());
    }
}
