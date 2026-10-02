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

namespace Espo\Core\MassAction\Actions;

use Espo\Core\Acl\Table;
use Espo\Core\Acl;
use Espo\Core\Currency\ConfigDataProvider as CurrencyConfigDataProvider;
use Espo\Core\Currency\Rates as CurrencyRates;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\MassAction\Data;
use Espo\Core\MassAction\MassAction;
use Espo\Core\MassAction\Params;
use Espo\Core\MassAction\QueryBuilder;
use Espo\Core\MassAction\Result;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\ORM\EntityManager;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\Tools\Currency\Conversion\EntityConverterFactory;
use RuntimeException;
use Throwable;

/**
 * @noinspection PhpUnused
 */
class MassConvertCurrency implements MassAction
{
    public function __construct(
        private QueryBuilder $queryBuilder,
        private Acl $acl,
        private EntityManager $entityManager,
        private Metadata $metadata,
        private CurrencyConfigDataProvider $configDataProvider,
        private EntityConverterFactory $converterFactory,
        private User $user,
        private Log $log,
    ) {}

    public function process(Params $params, Data $data): Result
    {
        $entityType = $params->getEntityType();

        if (!$this->acl->checkScope($entityType, Table::ACTION_EDIT)) {
            throw new Forbidden("No edit access for '{$entityType}'.");
        }

        if ($this->acl->getPermissionLevel(Acl\Permission::MASS_UPDATE) !== Table::LEVEL_YES) {
            throw new Forbidden("No mass-update permission.");
        }

        $this->checkFieldAccess($entityType);

        $dataRaw = $data->getRaw();

        if (empty($dataRaw->targetCurrency)) {
            throw new BadRequest("No target currency.");
        }

        if (isset($dataRaw->rates) && !is_object($dataRaw->rates)) {
            throw new BadRequest();
        }

        $baseCurrency = $this->configDataProvider->getBaseCurrency();
        $targetCurrency = $dataRaw->targetCurrency;

        $rates = $this->getRatesFromData($data) ??
            $this->configDataProvider->getCurrencyRates();

        if ($targetCurrency !== $baseCurrency && !$rates->hasRate($targetCurrency)) {
            throw new BadRequest("Target currency rate is not specified.");
        }

        $query = $this->queryBuilder->build($params);

        $collection = $this->entityManager
            ->getRDBRepository($entityType)
            ->clone($query)
            ->sth()
            ->find();

        $ids = [];
        $count = 0;

        $converter = $this->converterFactory->create($entityType);

        foreach ($collection as $entity) {
            if (!$this->acl->checkEntity($entity, Table::ACTION_EDIT)) {
                continue;
            }

            if (!$entity instanceof CoreEntity) {
                throw new RuntimeException("Only Core-Entity allowed.");
            }

            try {
                $converter->convert($entity, $targetCurrency, $rates);
            } catch (Forbidden|BadRequest|Conflict $e) {
                $this->log->info("Could not convert currency for {id}.", [
                    'id' => $entity->getId(),
                    'exception' => $e,
                ]);
            }

            $this->entityManager->saveEntity($entity, [SaveOption::MODIFIED_BY_ID => $this->user->getId()]);

            $ids[] = $entity->getId();
            $count++;
        }

        return new Result($count, $ids);
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
