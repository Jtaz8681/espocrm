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

namespace Espo\Modules\Crm\Tools\Opportunity\Report;

use DateInterval;
use Espo\Core\Field\Date;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Immutable.
 */
class DateRange
{
    public const TYPE_BETWEEN = 'between';
    public const TYPE_EVER = 'ever';
    public const TYPE_CURRENT_YEAR = 'currentYear';
    public const TYPE_CURRENT_QUARTER = 'currentQuarter';
    public const TYPE_CURRENT_MONTH = 'currentMonth';
    public const TYPE_CURRENT_FISCAL_YEAR = 'currentFiscalYear';
    public const TYPE_CURRENT_FISCAL_QUARTER = 'currentFiscalQuarter';

    private string $type;
    private ?Date $from;
    private ?Date $to;
    private int $fiscalYearShift;

    public function __construct(
        string $type,
        ?Date $from = null,
        ?Date $to = null,
        int $fiscalYearShift = 0
    ) {
        if ($type === self::TYPE_BETWEEN && (!$from || !$to)) {
            throw new InvalidArgumentException("Missing range dates.");
        }

        $this->type = $type;
        $this->from = $from;
        $this->to = $to;
        $this->fiscalYearShift = $fiscalYearShift;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFrom(): ?Date
    {
        return $this->from;
    }

    public function getTo(): ?Date
    {
        return $this->to;
    }

    public function withFiscalYearShift(int $fiscalYearShift): self
    {
        $obj = clone $this;
        $obj->fiscalYearShift = $fiscalYearShift;

        return $obj;
    }

    /**
     * @return array{?Date, ?Date}
     */
    public function getRange(): array
    {
        if ($this->type === self::TYPE_EVER) {
            return [null, null];
        }

        if ($this->type === self::TYPE_BETWEEN) {
            return [$this->from, $this->to];
        }

        $fiscalYearShift = $this->fiscalYearShift;

        switch ($this->type) {
            case self::TYPE_CURRENT_YEAR:
                $dt = Date::createToday()
                    ->modify('first day of January this year');

                return [
                    $dt,
                    $dt->addYears(1)
                ];

            case self::TYPE_CURRENT_QUARTER:
                $dt = Date::createToday();

                $quarter = (int) ceil($dt->getMonth() / 3);

                $dt = $dt
                    ->modify('first day of January this year')
                    ->addMonths(($quarter - 1) * 3);

                return [
                    $dt,
                    $dt->addMonths(3),
                ];

            case self::TYPE_CURRENT_MONTH:
                $dt = Date::createToday()
                    ->modify('first day of this month');

                return [
                    $dt,
                    $dt->addMonths(1),
                ];

            case self::TYPE_CURRENT_FISCAL_YEAR:
                $dt = Date::createToday()
                    ->modify('first day of January this year')
                    ->modify('+' . $fiscalYearShift . ' months');

                if (Date::createToday()->getMonth() < $fiscalYearShift + 1) {
                    $dt = $dt->addYears(-1);
                }

                return [
                    $dt,
                    $dt->addYears(1)
                ];

            case self::TYPE_CURRENT_FISCAL_QUARTER:
                $dt = Date::createToday()
                    ->modify('first day of January this year')
                    ->addMonths($fiscalYearShift);

                $month = Date::createToday()->getMonth();

                $quarterShift = (int) floor(($month - $fiscalYearShift - 1) / 3);

                if ($quarterShift) {
                    $dt = $dt->addMonths($quarterShift * 3);
                }

                return [
                    $dt,
                    $dt->add(new DateInterval('P3M'))
                ];
        }

        throw new UnexpectedValueException("Not supported range type");
    }
}
