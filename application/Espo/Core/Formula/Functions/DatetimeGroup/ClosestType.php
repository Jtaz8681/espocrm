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

use Espo\Core\Formula\{
    Functions\BaseFunction,
    ArgumentList,
};

use DateTime;
use DateTimeZone;

class ClosestType extends BaseFunction implements Di\ConfigAware
{
    use Di\ConfigSetter;

    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        if (count($args) < 3) {
            $this->throwTooFewArguments();
        }

        $value = $args[0];
        $type = $args[1];
        $target = $args[2];

        if (!in_array($type, ['time', 'minute', 'hour', 'date', 'dayOfWeek', 'month'])) {
            $this->throwBadArgumentType(1);
        }

        $inPast = false;

        if (count($args) > 3) {
            $inPast = $args[3];
        }

        $timezone = null;

        if (count($args) > 4) {
            $timezone = $args[4];
        }

        if (!$value) {
            return null;
        }

        if (!is_string($value)) {
            $this->throwBadArgumentType(1, 'string');
        }

        if (!$timezone) {
            $timezone = $this->config->get('timeZone');
        }

        $isDate = false;

        if (strlen($value) === 10) {
            $isDate = true;
            $value .= ' 00:00:00';
        }

        if (strlen($value) === 16) {
            $value .= ':00';
        }

        $format = 'Y-m-d H:i:s';

        /** @var DateTime $dt */
        $dt = DateTime::createFromFormat($format, $value, new DateTimeZone($timezone));

        $valueTimestamp = $dt->getTimestamp();

        if ($type === 'time') {
            if (!is_string($target)) {
                $this->throwBadArgumentType(3, 'string');
            }

            list($hour, $minute) = explode(':', $target);

            if (!$hour) {
                $hour = 0;
            }

            if (!$minute) {
                $minute = 0;
            }

            $dt->setTime((int) $hour, (int) $minute, 0);

            if ($valueTimestamp < $dt->getTimestamp()) {
                if ($inPast) {
                    $dt->modify('-1 day');
                }
            } else if ($valueTimestamp > $dt->getTimestamp()) {
                if (!$inPast) {
                    $dt->modify('+1 day');
                }
            }
        } else if ($type === 'hour') {
            $target = intval($target);
            $dt->setTime($target, 0, 0);

            if ($valueTimestamp < $dt->getTimestamp()) {
                if ($inPast) {
                    $dt->modify('-1 day');
                }
            } else if ($valueTimestamp > $dt->getTimestamp()) {
                if (!$inPast) {
                    $dt->modify('+1 day');
                }
            }
        } else if ($type === 'minute') {
            $target = intval($target);

            $dt->setTime(intval($dt->format('G')), intval($target), 0);

            if ($valueTimestamp < $dt->getTimestamp()) {
                if ($inPast) {
                    $dt->modify('-1 hour');
                }
            } else if ($valueTimestamp > $dt->getTimestamp()) {
                if (!$inPast) {
                    $dt->modify('+1 hour');
                }
            }
        } else if ($type === 'dayOfWeek') {
            $target = intval($target);
            $dt->setTime(0, 0, 0);

            $dayOfWeek = $dt->format('w');
            $dt->modify('-' . $dayOfWeek . ' days');
            $dt->modify('+' . $target . ' days');

            if ($valueTimestamp < $dt->getTimestamp()) {
                if ($inPast) {
                    $dt->modify('-1 week');
                }
            } else if ($valueTimestamp > $dt->getTimestamp()) {
                if (!$inPast) {
                    $dt->modify('+1 week');
                }
            }
        } else if ($type === 'date') {
            $target = intval($target);
            $dt->setTime(0, 0, 0);

            if ($inPast) {
                while (true) {
                    $date = intval($dt->format('d'));

                    if ($date === $target) {
                        break;
                    }

                    $dt->modify('-1 day');
                }
            } else {
                if ($valueTimestamp > $dt->getTimestamp()) {
                    $dt->modify('+1 day');
                }

                while (true) {
                    $date = intval($dt->format('d'));

                    if ($date === $target) {
                        break;
                    }

                    $dt->modify('+1 day');
                }
            }
        } else if ($type === 'month') {
            $target = intval($target);

            $dt->setTime(0, 0, 0);
            $days = intval($dt->format('d')) - 1;
            $dt->modify('-' . $days . ' days');

            if ($inPast) {
                while (true) {
                    $month = intval($dt->format('m'));

                    if ($month === $target) {
                        break;
                    }

                    $dt->modify('-1 month');
                }
            } else {
                if ($valueTimestamp > $dt->getTimestamp()) {
                    $dt->modify('+1 month');
                }

                while (true) {
                    $month = intval($dt->format('m'));

                    if ($month === $target) {
                        break;
                    }

                    $dt->modify('+1 month');
                }
            }
        }

        if ($isDate && in_array($type, ['time', 'minute', 'hour'])) {
            $isDate = false;
        }

        if (!$isDate) {
            $dt->setTimezone(new DateTimeZone('UTC'));

            return $dt->format('Y-m-d H:i');
        }

        return $dt->format('Y-m-d');
    }
}
