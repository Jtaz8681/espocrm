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

use Espo\Core\Acl\Table;
use Espo\Core\Currency\ConfigDataProvider;
use Espo\Core\Currency\Rates;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Acl;
use Espo\Core\Utils\Currency\DatabasePopulator;
use Espo\Core\Utils\DateTime;
use Espo\ORM\EntityManager;
use RuntimeException;

class RateService
{
    private const string SCOPE = 'Currency';

    public function __construct(
        private Acl $acl,
        private DatabasePopulator $databasePopulator,
        private ConfigDataProvider $configDataProvider,
        private SyncManager $syncManager,
        private RateEntryProvider $rateEntryProvider,
        private DateTime $dateTime,
        private EntityManager $entityManager,
    ) {}

    /**
     * @throws Forbidden
     */
    public function get(): Rates
    {
        $this->checkReadAccess();

        $rates = Rates::create($this->configDataProvider->getBaseCurrency());

        foreach ($this->configDataProvider->getCurrencyList() as $code) {
            $rates = $rates->withRate($code, $this->configDataProvider->getCurrencyRate($code));
        }

        return $rates;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function set(Rates $rates): void
    {
        $this->checkEditAccess();

        $codeList = $this->configDataProvider->getCurrencyList();
        $base = $this->configDataProvider->getBaseCurrency();

        foreach ($rates->toAssoc() as $code => $value) {
            if ($value < 0) {
                throw new BadRequest("Bad value.");
            }

            if (!in_array($code, $codeList) || $code === $base) {
                continue;
            }

            $this->writeOne($code, $value);
        }

        $this->syncManager->refreshCache();
        $this->databasePopulator->process();
    }

    private function writeOne(string $code, float $value): void
    {
        $date = $this->dateTime->getToday();

        try {
            $rateEntry = $this->rateEntryProvider->getRateEntryOnDate($code, $date) ??
                $this->rateEntryProvider->prepareNew($code, $date);
        } catch (Exceptions\NotEnabled $e) {
            throw new RuntimeException($e->getMessage(), previous: $e);
        }

        $rateEntry->setRate((string) $value);

        $this->entityManager->saveEntity($rateEntry);
    }

    /**
     * @throws Forbidden
     */
    private function checkReadAccess(): void
    {
        if (!$this->acl->check(self::SCOPE)) {
            throw new Forbidden();
        }

        if ($this->acl->getLevel(self::SCOPE, Table::ACTION_READ) !== Table::LEVEL_YES) {
            throw new Forbidden();
        }
    }

    /**
     * @throws Forbidden
     */
    private function checkEditAccess(): void
    {
        if (!$this->acl->check(self::SCOPE)) {
            throw new Forbidden();
        }

        if ($this->acl->getLevel(self::SCOPE, Table::ACTION_EDIT) !== Table::LEVEL_YES) {
            throw new Forbidden();
        }
    }
}
