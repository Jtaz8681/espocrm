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

namespace Espo\Classes\FieldSanitizers;

use DateTimeImmutable;
use DateTimeInterface;
use Espo\Core\Field\Date as DateValue;
use Espo\Core\FieldSanitize\Sanitizer;
use Espo\Core\FieldSanitize\Sanitizer\Data;
use Espo\Core\Utils\DateTime as DateTimeUtil;
use Exception;

/**
 * @noinspection PhpUnused
 */
class Date implements Sanitizer
{
    public function sanitize(Data $data, string $field): void
    {
        $value = $data->get($field);

        if ($value === null) {
            return;
        }

        try {
            DateValue::fromString($value);

            return;
        } catch (Exception) {}

        $dateTime = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $value);

        if ($dateTime === false) {
            return;
        }

        $value = $dateTime->format(DateTimeUtil::SYSTEM_DATE_FORMAT);

        $data->set($field, $value);
    }
}
