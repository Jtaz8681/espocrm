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

namespace tests\integration\Espo\Tools\WorkingTime;

use Espo\Core\Field\DateTime;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Entities\WorkingTimeCalendar;
use Espo\Tools\WorkingTime\CalendarUtilityFactory;
use tests\integration\Core\BaseTestCase;

class UtilityTest extends BaseTestCase
{
    public function testUtilityUser(): void
    {
        $em = $this->getEntityManager();

        $calendar = $em->createEntity(WorkingTimeCalendar::ENTITY_TYPE);

        $user = $this->createUser('test');
        $user->set('workingTimeCalendarId', $calendar->getId());
        $em->saveEntity($user);

        $utility = $this->getInjectableFactory()
            ->create(CalendarUtilityFactory::class)
            ->createForUser($user);

        $this->assertTrue($utility->isWorkingDay(DateTime::fromString('2024-02-23 00:00')));
        $this->assertFalse($utility->isWorkingDay(DateTime::fromString('2024-02-24 00:00')));
        $this->assertFalse($utility->isWorkingDay(DateTime::fromString('2024-02-25 00:00')));
        $this->assertTrue($utility->isWorkingDay(DateTime::fromString('2024-02-26 00:00')));
    }

    public function testUtilityGlobal(): void
    {
        $em = $this->getEntityManager();

        $calendar = $em->createEntity(WorkingTimeCalendar::ENTITY_TYPE);

        $configWriter = $this->getInjectableFactory()->create(ConfigWriter::class);
        $configWriter->set('workingTimeCalendarId', $calendar->getId());
        $configWriter->save();

        $utility = $this->getInjectableFactory()
            ->create(CalendarUtilityFactory::class)
            ->createGlobal();

        $this->assertTrue($utility->isWorkingDay(DateTime::fromString('2024-02-23 00:00')));
        $this->assertFalse($utility->isWorkingDay(DateTime::fromString('2024-02-24 00:00')));
        $this->assertFalse($utility->isWorkingDay(DateTime::fromString('2024-02-25 00:00')));
        $this->assertTrue($utility->isWorkingDay(DateTime::fromString('2024-02-26 00:00')));
    }
}
