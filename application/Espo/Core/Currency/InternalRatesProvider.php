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
use Espo\Core\Utils\Config\SystemConfig;
use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\DateTime;
use LogicException;
use stdClass;

/**
 * @internal
 */
class InternalRatesProvider
{
    private string $cacheKey = 'currencyRates';

    /** @var (stdClass&object{date: string, rates: stdClass})|null */
    private ?stdClass $data = null;

    public function __construct(
        private DataCache $dataCache,
        private SystemConfig $systemConfig,
        private DateTime $dateTime,
        private InternalRateEntryProvider $rateEntryProvider,
    ) {}

    /**
     * @return array<string, float>
     */
    public function get(string $base): array
    {
        $this->data ??= $this->getCachedData();

        $today = $this->dateTime->getToday();

        if (!$this->data || $this->data->date !== $today->toString()) {
            $this->data = $this->buildData($today, $base);

            $this->storeData();
        }

        if ($this->data === null) {
            throw new LogicException();
        }

        return get_object_vars($this->data->rates);
    }

    /**
     * @return (stdClass&object{date: string, rates: stdClass})|null
     */
    private function getCachedData(): ?stdClass
    {
        if (!$this->systemConfig->useCache()) {
            return null;
        }

        $cached = $this->dataCache->tryGet($this->cacheKey);

        if (!$cached instanceof stdClass) {
            return null;
        }

        if (!isset($cached->date) || !isset($cached->rates)) {
            $this->dataCache->clear($this->cacheKey);

            return null;
        }

        /** @var stdClass&object{date: string, rates: stdClass} */
        return $cached;
    }

    private function storeData(): void
    {
        if (!$this->systemConfig->useCache()) {
            return;
        }

        if (!$this->data instanceof stdClass) {
            throw new LogicException();
        }

        $this->dataCache->store($this->cacheKey, $this->data);
    }

    /**
     * @return stdClass&object{date: string, rates: stdClass}
     */
    private function buildData(Date $today, string $base): stdClass
    {
        $rates = [];

        foreach ($this->rateEntryProvider->getRateEntries($today, $base) as $rate) {
            $rates[$rate->getRecord()->getCode()] = (float) $rate->getRate();
        }

        return (object) [
            'date' => $today->toString(),
            'rates' => (object) $rates,
        ];
    }
}
