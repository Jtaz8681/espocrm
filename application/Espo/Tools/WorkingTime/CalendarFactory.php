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

namespace Espo\Tools\WorkingTime;

use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\InjectableFactory;
use Espo\Entities\Team;
use Espo\Entities\User;
use Espo\Entities\WorkingTimeCalendar;

class CalendarFactory
{
    public function __construct(private InjectableFactory $injectableFactory)
    {}

    public function createGlobal(): GlobalCalendar
    {
        return $this->injectableFactory->create(GlobalCalendar::class);
    }

    public function createForUser(User $user): UserCalendar
    {
        $binding = BindingContainerBuilder::create()
            ->bindInstance(User::class, $user)
            ->build();

        return $this->injectableFactory->createWithBinding(UserCalendar::class, $binding);
    }

    public function createForTeam(Team $team): TeamCalendar
    {
        $binding = BindingContainerBuilder::create()
            ->bindInstance(Team::class, $team)
            ->build();

        return $this->injectableFactory->createWithBinding(TeamCalendar::class, $binding);
    }

    /**
     * @since 8.4.0
     */
    public function create(WorkingTimeCalendar $calendar): Calendar
    {
        $binding = BindingContainerBuilder::create()
            ->bindInstance(WorkingTimeCalendar::class, $calendar)
            ->build();

        return $this->injectableFactory->createWithBinding(SpecificCalendar::class, $binding);
    }
}
