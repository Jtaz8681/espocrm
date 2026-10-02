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

namespace Espo\Core\Formula\Functions\ExtGroup\WorkingTimeGroup;

use Espo\Core\Field\DateTime;
use Espo\Core\Field\DateTimeOptional;
use Espo\Core\Formula\ArgumentList;

class FindClosestWorkingTimeType extends Base
{
    public function process(ArgumentList $args)
    {
        if (count($args) < 1) {
            $this->throwTooFewArguments(1);
        }

        /** @var mixed[] $evaluatedArgs */
        $evaluatedArgs = $this->evaluate($args);

        $stringValue = $evaluatedArgs[0];

        if (!is_string($stringValue)) {
            $this->throwBadArgumentType(1, 'string');
        }

        $calendar = $this->createCalendar($evaluatedArgs);

        $dateTime = DateTimeOptional::fromString($stringValue);

        if ($dateTime->isAllDay()) {
            $dateTime = $dateTime->withTimezone($calendar->getTimezone());
        }

        $dateTime = DateTime::fromDateTime($dateTime->getDateTime());

        $result = $this->createCalendarUtility($calendar)->findClosestWorkingTime($dateTime);

        if (!$result) {
            return null;
        }

        return $result->toString();
    }
}
