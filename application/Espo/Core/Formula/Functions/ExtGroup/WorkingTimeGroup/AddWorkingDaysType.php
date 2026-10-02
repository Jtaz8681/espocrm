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
use Espo\Core\Utils\DateTime as DateTimeUtil;

class AddWorkingDaysType extends Base
{
    public function process(ArgumentList $args)
    {
        if (count($args) < 2) {
            $this->throwTooFewArguments(2);
        }

        /** @var mixed[] $evaluatedArgs */
        $evaluatedArgs = $this->evaluate($args);

        $stringValue = $evaluatedArgs[0];
        $days = $evaluatedArgs[1];

        if (!is_string($stringValue)) {
            $this->throwBadArgumentType(1, 'string');
        }

        if (!is_int($days) && !is_float($days)) {
            $this->throwBadArgumentType(2, 'int');
        }

        if (is_float($days)) {
            $days = (int) $days;
        }

        if ($days <= 0) {
            $this->throwBadArgumentValue(2, 'Days value should be greater than 0.');
        }

        $calendar = $this->createCalendar($evaluatedArgs, 2);

        $dateTime = DateTimeOptional::fromString($stringValue);

        $isAllDay = $dateTime->isAllDay();

        if ($isAllDay) {
            $dateTime = $dateTime->withTimezone($calendar->getTimezone());
        }

        $dateTime = DateTime::fromDateTime($dateTime->toDateTime());

        $result = $this->createCalendarUtility($calendar)->addWorkingDays($dateTime, $days);

        if (!$result) {
            return null;
        }

        if ($isAllDay) {
            return $result->toDateTime()->format(DateTimeUtil::SYSTEM_DATE_FORMAT);
        }

        return $result->toString();
    }
}
