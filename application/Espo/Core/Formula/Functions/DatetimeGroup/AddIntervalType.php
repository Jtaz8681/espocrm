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

namespace Espo\Core\Formula\Functions\DatetimeGroup;

use Espo\Core\Di;
use Espo\Core\Formula\Exceptions\FunctionRuntimeError;
use Espo\Core\Utils\DateTime as DateTimeUtil;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;
use DateTime;
use DateMalformedStringException;
use Exception;

abstract class AddIntervalType extends BaseFunction implements Di\DateTimeAware
{
    use Di\DateTimeSetter;

    protected bool $timeOnly = false;
    protected string $intervalTypeString;

    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        if (count($args) < 2) {
            $this->throwTooFewArguments();
        }

        $dateTimeString = $args[0];

        if (!$dateTimeString) {
            return null;
        }

        if (!is_string($dateTimeString)) {
            $this->throwBadArgumentType(1, 'string');
        }

        $interval = $args[1];

        if (!is_numeric($interval)) {
            $this->throwBadArgumentType(2, 'numeric');
        }

        $isTime = false;
        if (strlen($dateTimeString) > 10) {
            $isTime = true;
        }

        if ($this->timeOnly && !$isTime) {
            $dateTimeString .= ' 00:00:00';
            $isTime = true;
        }

        try {
            $dateTime = new DateTime($dateTimeString);
        } catch (Exception) {
            throw new FunctionRuntimeError("Bad date-time value '$dateTimeString'.");
        }

        $modifier = ($interval > 0 ? '+' : '') . $interval . ' ' . $this->intervalTypeString;

        try {
            $dateTime->modify($modifier);
        } catch (DateMalformedStringException $e) {
            throw new FunctionRuntimeError($e->getMessage(), previous: $e);
        }

        if ($isTime) {
            return $dateTime->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);
        } else {
            return $dateTime->format(DateTimeUtil::SYSTEM_DATE_FORMAT);
        }
    }
}
