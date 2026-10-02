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

namespace Espo\Core\Action\Actions;

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Action\Action;
use Espo\Core\Action\Data;
use Espo\Core\Action\Params;
use Espo\Core\Currency\ConfigDataProvider as CurrencyConfigDataProvider;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\Currency\Rates as CurrencyRates;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\ORM\EntityManager;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\Tools\Currency\Conversion\EntityConverterFactory;
use RuntimeException;

/**
 * @noinspection PhpUnused
 */
class ConvertCurrency implements Action
{
    public function __construct(
        private EntityConverterFactory $converterFactory,
        private Acl $acl,
        private EntityManager $entityManager,
        private Metadata $metadata,
        private CurrencyConfigDataProvider $configDataProvider,
        private User $user
    ) {}

    public function process(Params $params, Data $data): void
    {
        $entityType = $params->getEntityType();
        $id = $params->getId();

        if (!$this->acl->checkScope($entityType, Acl\Table::ACTION_EDIT)) {
            throw new Forbidden();
        }

        $this->checkFieldAccess($entityType);

        $baseCurrency = $this->configDataProvider->getBaseCurrency();
        $targetCurrency = $data->get('targetCurrency');

        if (!$targetCurrency) {
            throw new BadRequest("No target currency.");
        }

        $rates = $this->getRatesFromData($data) ??
            $this->configDataProvider->getCurrencyRates();

        if ($targetCurrency !== $baseCurrency && !$rates->hasRate($targetCurrency)) {
            throw new BadRequest("Target currency rate is not specified.");
        }

        $entity = $this->entityManager->getEntityById($entityType, $id);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden("No 'edit' access.");
        }

        if (!$entity instanceof CoreEntity) {
            throw new RuntimeException("Only Core-Entity allowed.");
        }

        $converter = $this->converterFactory->create($entityType);

        $converter->convert($entity, $targetCurrency, $rates);

        $this->entityManager->saveEntity($entity, [SaveOption::MODIFIED_BY_ID => $this->user->getId()]);
    }

    private function getRatesFromData(Data $data): ?CurrencyRates
    {
        if ($data->get('rates') === null) {
            return null;
        }

        $baseCurrency = $this->configDataProvider->getBaseCurrency();

        $ratesArray = get_object_vars($data->get('rates'));
        $ratesArray[$baseCurrency] = 1.0;

        return CurrencyRates::fromAssoc($ratesArray, $baseCurrency);
    }

    /**
     * @throws Forbidden
     */
    private function checkFieldAccess(string $entityType): void
    {
        /** @var string[] $requiredFieldList */
        $requiredFieldList = $this->metadata->get(['scopes', $entityType, 'currencyConversionAccessRequiredFieldList']);

        if ($requiredFieldList === null) {
            return;
        }

        foreach ($requiredFieldList as $field) {
            if (!$this->acl->checkField($entityType, $field, Table::ACTION_EDIT)) {
                throw new Forbidden("No edit access to field `$field`.");
            }
        }
    }
}
