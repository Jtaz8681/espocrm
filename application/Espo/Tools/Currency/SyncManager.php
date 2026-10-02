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
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Core\Utils\Config\SystemConfig;
use Espo\Core\Utils\DataCache;
use Espo\Entities\CurrencyRecord;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\UpdateBuilder;
use Espo\Tools\Currency\Exceptions\NotEnabled;

/**
 * @since 9.3.0
 * @internal
 */
class SyncManager
{
    private string $cacheKey = 'currencyRates';

    public function __construct(
        private ConfigDataProvider $configDataProvider,
        private EntityManager $entityManager,
        private ConfigWriter $configWriter,
        private RateEntryProvider $rateEntryProvider,
        private DataCache $dataCache,
        private SystemConfig $systemConfig,
    ) {}

    public function sync(): void
    {
        $this->entityManager->getTransactionManager()->run(function () {
            $this->syncInTransaction();
        });

        $this->refreshCache();
    }

    private function syncInTransaction(): void
    {
        $this->lock();

        foreach ($this->configDataProvider->getCurrencyList() as $code) {
            $this->syncCode($code);
        }

        $this->deactivateNotListed();
    }

    private function syncCode(string $code): void
    {
        $record = $this->entityManager
            ->getRDBRepositoryByClass(CurrencyRecord::class)
            ->where([CurrencyRecord::FIELD_CODE => $code])
            ->findOne();

        if (!$record) {
            $record = $this->entityManager->getRDBRepositoryByClass(CurrencyRecord::class)->getNew();

            $record->setCode($code);
        }

        $record->setStatus(CurrencyRecord::STATUS_ACTIVE);

        $this->entityManager->saveEntity($record);
    }

    private function deactivateNotListed(): void
    {
        $list = $this->configDataProvider->getCurrencyList();

        $updateQuery = UpdateBuilder::create()
            ->in(CurrencyRecord::ENTITY_TYPE)
            ->set([
                CurrencyRecord::FIELD_STATUS => CurrencyRecord::STATUS_INACTIVE,
            ])
            ->where([
                CurrencyRecord::FIELD_CODE . '!=' => $list,
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($updateQuery);
    }

    private function lock(): void
    {
        $this->entityManager
            ->getRDBRepositoryByClass(CurrencyRecord::class)
            ->forUpdate()
            ->sth()
            ->select(Attribute::ID)
            ->find();
    }

    public function refreshCache(): void
    {
        $this->entityManager->getTransactionManager()->run(function () {
            $this->syncToConfigInTransaction();
        });

        $this->clearCache();
    }

    private function syncToConfigInTransaction(): void
    {
        $this->lock();

        $rates = $this->configDataProvider->getCurrencyRates()->toAssoc();

        $this->configWriter->set('currencyRates', $rates);
        $this->configWriter->save();
    }

    /**
     * @throws NotEnabled
     */
    public function updateCode(string $code): void
    {
        $rates = $this->configDataProvider->getCurrencyRates()->toAssoc();

        $rate = $this->rateEntryProvider->getRate($code) ?? '1.0';

        $rates[$code] = (float) $rate;

        $this->configWriter->set('currencyRates', $rates);
        $this->configWriter->save();

        $this->clearCache();
    }

    private function clearCache(): void
    {
        if (!$this->systemConfig->useCache()) {
            return;
        }

        $this->dataCache->clear($this->cacheKey);
    }
}
