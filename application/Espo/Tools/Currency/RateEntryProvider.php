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

namespace Espo\Tools\Currency;

use Espo\Core\Currency\ConfigDataProvider;
use Espo\Core\Currency\InternalRateEntryProvider;
use Espo\Core\Field\Date;
use Espo\Core\Utils\DateTime;
use Espo\Entities\CurrencyRecord;
use Espo\Entities\CurrencyRecordRate;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Order;
use Espo\Tools\Currency\Exceptions\NotEnabled;
use WeakMap;

/**
 * @since 9.3.0
 */
class RateEntryProvider
{
    /** @var WeakMap<CurrencyRecord, ?CurrencyRecordRate> */
    private WeakMap $map;

    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private EntityManager $entityManager,
        private DateTime $dateTime,
        private InternalRateEntryProvider $internalRateEntryProvider,
    ) {
        $this->map = new WeakMap();
    }

    public function getCurrentRateEntry(CurrencyRecord $record): ?CurrencyRecordRate
    {
        if (!$this->map->offsetExists($record)) {
            $this->map[$record] = $this->entityManager
                ->getRDBRepositoryByClass(CurrencyRecordRate::class)
                ->where([
                    CurrencyRecordRate::ATTR_RECORD_ID => $record->getId(),
                    CurrencyRecordRate::FIELD_BASE_CODE => $this->configDataProvider->getBaseCurrency(),
                    CurrencyRecordRate::FIELD_DATE . '<=' => $this->dateTime->getToday()->toString(),
                ])
                ->order(CurrencyRecordRate::FIELD_DATE, Order::DESC)
                ->findOne();
        }

        return $this->map[$record];
    }

    /**
     * @throws NotEnabled
     */
    public function prepareNew(string $code, Date $date): CurrencyRecordRate
    {
        $record = $this->getRecordByCode($code);

        $entry = $this->entityManager->getRDBRepositoryByClass(CurrencyRecordRate::class)->getNew();

        $entry
            ->setRecord($record)
            ->setDate($date);

        return $entry;
    }

    private function getRateEntryForRecord(CurrencyRecord $record, ?Date $date = null): ?CurrencyRecordRate
    {
        $date ??= $this->dateTime->getToday();
        $base = $this->configDataProvider->getBaseCurrency();

        return $this->internalRateEntryProvider->getRateEntryForRecord($record, $date, $base);
    }

    /**
     * Get rate against the base currency by a record.
     *
     * @return ?numeric-string
     */
    public function getRateForRecord(CurrencyRecord $record, ?Date $date = null): ?string
    {
        $rateEntry = $this->getRateEntryForRecord($record, $date);

        return $rateEntry?->getRate();
    }

    /**
     * Get rate against the base currency.
     *
     * @param string $code
     * @return ?numeric-string
     * @throws NotEnabled
     */
    public function getRate(string $code): ?string
    {
        $record = $this->getRecordByCode($code);

        return $this->getRateForRecord($record);
    }

    /**
     * @throws NotEnabled
     */
    public function getRateEntryOnDate(string $code, Date $date): ?CurrencyRecordRate
    {
        $record = $this->getRecordByCode($code);

        return $this->entityManager
            ->getRDBRepositoryByClass(CurrencyRecordRate::class)
            ->where([
                CurrencyRecordRate::ATTR_RECORD_ID => $record->getId(),
                CurrencyRecordRate::FIELD_BASE_CODE => $this->configDataProvider->getBaseCurrency(),
                CurrencyRecordRate::FIELD_DATE  => $date->toString(),
            ])
            ->order(CurrencyRecordRate::FIELD_DATE, Order::DESC)
            ->findOne();
    }

    /**
     * @since 9.3.0
     * @throws NotEnabled
     * @noinspection PhpUnused
     */
    public function getRateEntryOnAsOfDate(string $code, Date $date): ?CurrencyRecordRate
    {
        $record = $this->getRecordByCode($code);

        return $this->entityManager
            ->getRDBRepositoryByClass(CurrencyRecordRate::class)
            ->where([
                CurrencyRecordRate::ATTR_RECORD_ID => $record->getId(),
                CurrencyRecordRate::FIELD_BASE_CODE => $this->configDataProvider->getBaseCurrency(),
                CurrencyRecordRate::FIELD_DATE . '<=' => $date->toString(),
            ])
            ->order(CurrencyRecordRate::FIELD_DATE, Order::DESC)
            ->findOne();
    }

    /**
     * @throws NotEnabled
     */
    private function getRecordByCode(string $code): CurrencyRecord
    {
        $record = $this->entityManager
            ->getRDBRepositoryByClass(CurrencyRecord::class)
            ->where([
                CurrencyRecord::FIELD_CODE => $code,
                CurrencyRecord::FIELD_STATUS => CurrencyRecord::STATUS_ACTIVE,
            ])
            ->findOne();

        if (!$record) {
            throw new NotEnabled("Currency $code is not enabled.");
        }

        return $record;
    }
}
