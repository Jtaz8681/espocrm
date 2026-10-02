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

namespace Espo\Core\Mail\Event;

use ICal\Event as ICalEvent;
use ICal\ICal as U01jmg3ICal;

use RuntimeException;

class EventFactory
{
    public static function createFromU01jmg3Ical(U01jmg3ICal $ical): Event
    {
        /* @var ?ICalEvent $event */
        $event = $ical->events()[0] ?? null;

        if (!$event) {
            throw new RuntimeException();
        }

        $dateStart = $event->dtstart_tz ?? null;
        $dateEnd = $event->dtend_tz ?? null;

        $isAllDay = strlen($event->dtstart) === 8;

        if ($isAllDay) {
            $dateStart = $event->dtstart ?? null;
            $dateEnd = $event->dtend ?? null;
        }

        return Event::create()
            ->withUid($event->uid ?? null)
            ->withIsAllDay($isAllDay)
            ->withDateStart($dateStart)
            ->withDateEnd($dateEnd)
            ->withName($event->summary ?? null)
            ->withLocation($event->location ?? null)
            ->withDescription($event->description ?? null)
            ->withTimezone($ical->calendarTimeZone() ?? null) /** @phpstan-ignore-line */
            ->withOrganizer($event->organizer ?? null)
            ->withAttendees($event->attendee ?? null);
    }
}
