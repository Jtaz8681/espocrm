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

class CalendarUtilityFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private CalendarFactory $calendarFactory
    ) {}

    public function create(Calendar $calendar): CalendarUtility
    {
        return $this->injectableFactory->createWithBinding(
            CalendarUtility::class,
            BindingContainerBuilder::create()
                ->bindInstance(Calendar::class, $calendar)
                ->build()
        );
    }

    public function createForUser(User $user): CalendarUtility
    {
        $calendar = $this->calendarFactory->createForUser($user);

        return $this->create($calendar);
    }

    public function createForTeam(Team $team): CalendarUtility
    {
        $calendar = $this->calendarFactory->createForTeam($team);

        return $this->create($calendar);
    }

    public function createGlobal(): CalendarUtility
    {
        $calendar = $this->calendarFactory->createGlobal();

        return $this->create($calendar);
    }
}
