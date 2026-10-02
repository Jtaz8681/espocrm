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

namespace Espo\Core\Currency;

use Espo\Core\Field\Date;
use Espo\Core\ORM\EntityManagerProxy;
use Espo\Entities\CurrencyRecord;
use Espo\Entities\CurrencyRecordRate;
use Espo\ORM\Query\Part\Order;
use Traversable;

/**
 * @internal
 */
class InternalRateEntryProvider
{
    public function __construct(
        private EntityManagerProxy $entityManager,
    ) {}

    /**
     * @return CurrencyRecordRate[]
     */
    public function getRateEntries(Date $date, string $base): array
    {
        $rates = [];

        foreach ($this->getActiveCurrencyRecords() as $record) {
            $rate = $this->getRateEntryForRecord($record, $date, $base);

            if ($rate) {
                $rates[] = $rate;
            }
        }

        return $rates;
    }

    /**
     * @return Traversable<int, CurrencyRecord>
     */
    private function getActiveCurrencyRecords(): Traversable
    {
        return $this->entityManager
            ->getRDBRepositoryByClass(CurrencyRecord::class)
            ->where([
                CurrencyRecord::FIELD_STATUS => CurrencyRecord::STATUS_ACTIVE,
            ])
            ->find();
    }

    public function getRateEntryForRecord(CurrencyRecord $record, Date $date, string $base): ?CurrencyRecordRate
    {
        return $this->entityManager
            ->getRDBRepositoryByClass(CurrencyRecordRate::class)
            ->where([
                CurrencyRecordRate::ATTR_RECORD_ID => $record->getId(),
                CurrencyRecordRate::FIELD_BASE_CODE => $base,
                CurrencyRecordRate::FIELD_DATE . '<=' => $date->toString(),
            ])
            ->order(CurrencyRecordRate::FIELD_DATE, Order::DESC)
            ->findOne();
    }
}
