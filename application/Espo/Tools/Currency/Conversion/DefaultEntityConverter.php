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

namespace Espo\Tools\Currency\Conversion;

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Currency\Converter;
use Espo\Core\Currency\Rates;
use Espo\Core\Field\Currency;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use LogicException;

/**
 * @implements EntityConverter<CoreEntity>
 */
class DefaultEntityConverter implements EntityConverter
{
    public function __construct(
        private Converter $converter,
        private EntityManager $entityManager,
        private Metadata $metadata,
        private Acl $acl
    ) {}

    /**
     * @param CoreEntity $entity
     */
    public function convert(Entity $entity, string $targetCurrency, Rates $rates): void
    {
        $entityDefs = $this->entityManager
            ->getDefs()
            ->getEntity($entity->getEntityType());

        foreach ($this->getFieldList($entity->getEntityType()) as $field) {
            $disabled = $entityDefs->getField($field)->getParam('conversionDisabled');

            if ($disabled) {
                continue;
            }

            $value = $entity->getValueObject($field);

            if (!$value) {
                continue;
            }

            if (!$value instanceof Currency) {
                throw new LogicException();
            }

            if ($targetCurrency === $value->getCode()) {
                continue;
            }

            $convertedValue = $this->converter->convertWithRates($value, $targetCurrency, $rates);

            $entity->setValueObject($field, $convertedValue);
        }
    }

    /**
     * @return string[]
     */
    private function getFieldList(string $entityType): array
    {
        $resultList = [];

        /** @var string[] $requiredFieldList */
        $requiredFieldList = $this->metadata->get(['scopes', $entityType, 'currencyConversionAccessRequiredFieldList']);

        $allFields = $requiredFieldList !== null;

        $fieldDefsList = $this->entityManager
            ->getDefs()
            ->getEntity($entityType)
            ->getFieldList();

        foreach ($fieldDefsList as $fieldDefs) {
            $field = $fieldDefs->getName();
            $type = $fieldDefs->getType();

            if ($type !== FieldType::CURRENCY) {
                continue;
            }

            if (
                !$allFields &&
                !$this->acl->checkField($entityType, $field, Table::ACTION_EDIT)
            ) {
                continue;
            }

            $resultList[] = $field;
        }

        return $resultList;
    }
}
