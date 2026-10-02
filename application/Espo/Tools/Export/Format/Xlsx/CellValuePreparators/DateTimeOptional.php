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

namespace Espo\Tools\Export\Format\Xlsx\CellValuePreparators;

use Espo\Core\Field\DateTime as DateTimeValue;
use Espo\Core\Field\Date as DateValue;
use Espo\Core\Utils\Config;
use Espo\ORM\Entity;
use Espo\Tools\Export\Format\CellValuePreparator;

use DateTimeZone;

class DateTimeOptional implements CellValuePreparator
{
    private string $timezone;

    public function __construct(Config\ApplicationConfig $applicationConfig)
    {
        $this->timezone = $applicationConfig->getTimeZone();
    }

    public function prepare(Entity $entity, string $name): DateTimeValue|DateValue|null
    {
        $dateValue = $entity->get($name . 'Date');

        if ($dateValue !== null) {
            return DateValue::fromString($dateValue);
        }

        $value = $entity->get($name);

        if (!$value) {
            return null;
        }

        return DateTimeValue::fromString($value)
            ->withTimezone(
                new DateTimeZone($this->timezone)
            );
    }
}
