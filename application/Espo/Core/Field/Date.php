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

namespace Espo\Core\Field;

use Espo\Core\Field\DateTime\DateTimeable;

use DateTimeImmutable;
use DateTimeInterface;
use DateInterval;
use DateTimeZone;
use InvalidArgumentException;
use RuntimeException;

/**
 * A date value object. Immutable.
 */
class Date implements DateTimeable
{
    private string $value;
    private DateTimeImmutable $dateTime;

    private const string SYSTEM_FORMAT = 'Y-m-d';

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $value)
    {
        if (!$value) {
            throw new InvalidArgumentException("Empty value.");
        }

        $this->value = $value;

        $parsedValue = DateTimeImmutable::createFromFormat(
            '!' . self::SYSTEM_FORMAT,
            $value,
            new DateTimeZone('UTC')
        );

        if ($parsedValue === false) {
            throw new InvalidArgumentException("Bad value.");
        }

        $this->dateTime = $parsedValue;

        if ($this->value !== $this->dateTime->format(self::SYSTEM_FORMAT)) {
            throw new InvalidArgumentException("Bad value.");
        }
    }

    /**
     * Get a string value in `Y-m-d` format.
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Get DateTimeImmutable.
     */
    public function toDateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }

    /**
     * Get a timestamp.
     */
    public function toTimestamp(): int
    {
        return $this->dateTime->getTimestamp();
    }

    /**
     * Get a year.
     */
    public function getYear(): int
    {
        return (int) $this->dateTime->format('Y');
    }

    /**
     * Get a month.
     */
    public function getMonth(): int
    {
        return (int) $this->dateTime->format('n');
    }

    /**
     * Get a day (of month).
     */
    public function getDay(): int
    {
        return (int) $this->dateTime->format('j');
    }

    /**
     * Get a day of week. 0 (for Sunday) through 6 (for Saturday).
     */
    public function getDayOfWeek(): int
    {
        return (int) $this->dateTime->format('w');
    }

    /**
     * Clones and modifies.
     */
    public function modify(string $modifier): self
    {
        /** @var DateTimeImmutable|false $dateTime */
        $dateTime = $this->dateTime->modify($modifier);

        if (!$dateTime) {
            throw new RuntimeException("Modify failure.");
        }

        return self::fromDateTime($dateTime);
    }

    /**
     * Clones and adds an interval.
     */
    public function add(DateInterval $interval): self
    {
        $dateTime = $this->dateTime->add($interval);

        return self::fromDateTime($dateTime);
    }

    /**
     * Clones and subtracts an interval.
     */
    public function subtract(DateInterval $interval): self
    {
        $dateTime = $this->dateTime->sub($interval);

        return self::fromDateTime($dateTime);
    }

    /**
     * Add days.
     */
    public function addDays(int $days): self
    {
        $modifier = ($days >= 0 ? '+' : '-') . abs($days) . ' days';

        return $this->modify($modifier);
    }

    /**
     * Add months.
     */
    public function addMonths(int $months): self
    {
        $modifier = ($months >= 0 ? '+' : '-') . abs($months) . ' months';

        return $this->modify($modifier);
    }

    /**
     * Add years.
     */
    public function addYears(int $years): self
    {
        $modifier = ($years >= 0 ? '+' : '-') . abs($years) . ' years';

        return $this->modify($modifier);
    }

    /**
     * A difference between another object (date or date-time) and self.
     */
    public function diff(DateTimeable $other): DateInterval
    {
        return $this->toDateTime()->diff($other->toDateTime());
    }

    /**
     * Whether greater than a given value.
     */
    public function isGreaterThan(DateTimeable $other): bool
    {
        return $this->toDateTime() > $other->toDateTime();
    }

    /**
     * Whether less than a given value.
     */
    public function isLessThan(DateTimeable $other): bool
    {
        return $this->toDateTime() < $other->toDateTime();
    }

    /**
     * Whether equals to a given value.
     */
    public function isEqualTo(DateTimeable $other): bool
    {
        return $this->toDateTime() == $other->toDateTime();
    }

    /**
     * Whether less than or equals to a given value.
     * @since 9.0.0
     */
    public function isLessThanOrEqualTo(DateTimeable $other): bool
    {
        return $this->isLessThan($other) || $this->isEqualTo($other);
    }

    /**
     * Whether greater than or equals to a given value.
     * @since 9.0.0
     */
    public function isGreaterThanOrEqualTo(DateTimeable $other): bool
    {
        return $this->isGreaterThan($other) || $this->isEqualTo($other);
    }

    /**
     * Create a today.
     */
    public static function createToday(?DateTimeZone $timezone = null): self
    {
        $now = new DateTimeImmutable();

        if ($timezone) {
            $now = $now->setTimezone($timezone);
        }

        return self::fromDateTime($now);
    }

    /**
     * Create from a string with a date in `Y-m-d` format.
     *
     * @throws InvalidArgumentException
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Create from a DateTimeInterface.
     */
    public static function fromDateTime(DateTimeInterface $dateTime): self
    {
        $value = $dateTime->format(self::SYSTEM_FORMAT);

        return new self($value);
    }

    /**
     * @deprecated As of v8.1. Use `toString` instead.
     * @todo Remove in v11.0.
     */
    public function getString(): string
    {
        return $this->toString();
    }

    /**
     * @deprecated As of v8.1. Use `toDateTime` instead.
     * @todo Remove in v11.0.
     */
    public function getDateTime(): DateTimeImmutable
    {
        return $this->toDateTime();
    }

    /**
     * @deprecated As of v8.1. Use `toTimestamp` instead.
     * @todo Remove in v11.0.
     */
    public function getTimestamp(): int
    {
        return $this->toTimestamp();
    }
}
