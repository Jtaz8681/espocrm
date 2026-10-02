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

namespace tests\integration\Espo\Currency;

use Espo\Core\Exceptions\Error;
use Espo\Core\Field\Date;
use Espo\Core\Formula\Manager as FormulaManager;
use Espo\Modules\Crm\Entities\Lead;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\Tools\Currency\RateEntryProvider;
use Espo\Tools\Currency\RateService;
use Espo\Core\Currency\Rates;
use Espo\Core\Field\Currency;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Tools\Currency\SyncManager;
use integration\Core\NoTransaction;
use tests\integration\Core\BaseTestCase;

class CurrencyTest extends BaseTestCase
{
    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testSetCurrencyRates(): void
    {
        $factory = $this->getInjectableFactory();

        $configWriter = $factory->create(ConfigWriter::class);

        $configWriter->set('currencyList', ['USD', 'EUR']);
        $configWriter->set('defaultCurrency', 'USD');
        $configWriter->set('baseCurrency', 'USD');
        $configWriter->set('currencyRates', [
            'EUR' => 1.2,
        ]);

        $configWriter->save();

        $this->getInjectableFactory()->create(SyncManager::class)->sync();

        $service = $factory->create(RateService::class);

        $rates = Rates::fromAssoc(['EUR' => 1.3], '___');

        $service->set($rates);

        $newRates = $service->get();

        $this->assertEquals(1.3, $newRates->getRate('EUR'));
    }

    /**
     * @throws Error
     */
    #[NoTransaction]
    public function testDecimal1(): void
    {
        $this->getMetadata()->set('entityDefs', 'Lead', [
            'fields' => [
                'testCurrency' => [
                    'type' => 'currency',
                    'decimal' => true,
                ]
            ]
        ]);

        $this->getMetadata()->save();
        $this->getDataManager()->rebuild();
        $this->reCreateApplication();

        $em = $this->getEntityManager();

        $value = Currency::create('10.1', 'USD')
            ->add(Currency::create('0.1', 'USD'));

        $lead = $em->getRDBRepositoryByClass(Lead::class)->getNew();

        $lead->setValueObject('testCurrency', $value);

        $value = $lead->getValueObject('testCurrency');
        $this->assertInstanceOf(Currency::class, $value);

        $this->assertEquals('10.2000', $value->getAmountAsString());

        $this->getEntityManager()->saveEntity($lead);

        /** @var Lead $lead */
        $lead = $this->getEntityManager()->getEntityById(Lead::ENTITY_TYPE, $lead->getId());

        $value = $lead->getValueObject('testCurrency');

        $this->assertInstanceOf(Currency::class, $value);
        $this->assertEquals('10.2000', $value->getAmountAsString());
        $this->assertEquals(0, $value->compare(Currency::create('10.2', 'USD')));

        //

        $lead = $em->getRDBRepositoryByClass(Lead::class)->getNew();

        $value = Currency::create('10', 'USD');

        $lead->setValueObject('testCurrency', $value);

        $value = $lead->getValueObject('testCurrency');
        $this->assertInstanceOf(Currency::class, $value);

        $this->assertEquals('10.0000', $value->getAmountAsString());

        //

        $lead = $em->getRDBRepositoryByClass(Lead::class)->getNew();

        $lead->setValueObject('testCurrency', null);

        $value = $lead->getValueObject('testCurrency');

        $this->assertEquals(null, $value);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testFormulaConvert(): void
    {
        $formulaManager = $this->getContainer()->getByClass(FormulaManager::class);
        $em = $this->getEntityManager();

        $configWriter = $this->getInjectableFactory()->create(ConfigWriter::class);

        $configWriter->set('currencyList', ['USD', 'EUR']);
        $configWriter->set('defaultCurrency', 'USD');
        $configWriter->set('baseCurrency', 'USD');
        $configWriter->save();

        $syncManager = $this->getInjectableFactory()->create(SyncManager::class);

        $syncManager->sync();

        $rate = $this->getInjectableFactory()->create(RateEntryProvider::class)
            ->prepareNew('EUR', Date::createToday()->addDays(-2));

        $rate->setRate('2.0');

        $em->saveEntity($rate);

        $syncManager->refreshCache();

        $script = "ext\\currency\\convert('0.5', 'EUR')";
        $value = $formulaManager->run($script);
        $this->assertEquals(1.0, (float) $value);

        $script = "ext\\currency\\convert('0.5', 'EUR', 'USD')";
        $value = $formulaManager->run($script);
        $this->assertEquals(1.0, (float) $value);
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testDefaultCurrencyJoinMode(): void
    {
        $configWriter = $this->getInjectableFactory()->create(ConfigWriter::class);

        $configWriter->set('currencyNoJoinMode', false);
        $configWriter->set('currencyList', ['USD', 'EUR']);
        $configWriter->set('defaultCurrency', 'USD');
        $configWriter->set('baseCurrency', 'EUR');
        $configWriter->save();

        $this->getInjectableFactory()->create(SyncManager::class)->sync();

        $rateService = $this->getInjectableFactory()->create(RateService::class);
        $rateService->set(Rates::fromAssoc([
            'USD' => 0.5,
        ], 'EUR'));

        // Full rebuild caused a problem with the test transaction in PG.
        $this->getDataManager()->rebuildMetadata();

        $this->reCreateApplication(reuse: true);

        $em = $this->getEntityManager();

        $opp = $em->getRDBRepositoryByClass(Opportunity::class)->getNew();
        $opp->setAmount(Currency::create('10.0', 'USD'));
        $em->saveEntity($opp);
        $em->refreshEntity($opp);

        $this->assertEquals(10, $opp->get('amountConverted'));

        //

        $opp = $em->getRDBRepositoryByClass(Opportunity::class)->getNew();
        $opp->setAmount(Currency::create('10.0', 'EUR'));
        $em->saveEntity($opp);
        $em->refreshEntity($opp);

        $this->assertEquals(20, $opp->get('amountConverted'));
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function testDefaultCurrencyNoJoinMode(): void
    {
        $configWriter = $this->getInjectableFactory()->create(ConfigWriter::class);

        $configWriter->set('currencyNoJoinMode', true);
        $configWriter->set('currencyList', ['USD', 'EUR']);
        $configWriter->set('defaultCurrency', 'USD');
        $configWriter->set('baseCurrency', 'EUR');
        $configWriter->save();

        $this->getInjectableFactory()->create(SyncManager::class)->sync();

        $rateService = $this->getInjectableFactory()->create(RateService::class);
        $rateService->set(Rates::fromAssoc([
            'USD' => 0.5,
        ], 'EUR'));

        // Full rebuild caused a problem with the test transaction in PG.
        $this->getDataManager()->rebuildMetadata();

        $this->reCreateApplication(reuse: true);

        $em = $this->getEntityManager();

        $opp = $em->getRDBRepositoryByClass(Opportunity::class)->getNew();
        $opp->setAmount(Currency::create('10.0', 'USD'));
        $em->saveEntity($opp);
        $em->refreshEntity($opp);

        $this->assertEquals(10, $opp->get('amountConverted'));

        //

        $opp = $em->getRDBRepositoryByClass(Opportunity::class)->getNew();
        $opp->setAmount(Currency::create('10.0', 'EUR'));
        $em->saveEntity($opp);
        $em->refreshEntity($opp);

        $this->assertEquals(20, $opp->get('amountConverted'));
    }
}
