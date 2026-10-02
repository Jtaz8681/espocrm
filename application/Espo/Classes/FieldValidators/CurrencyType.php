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

namespace Espo\Classes\FieldValidators;

use Espo\Core\Field\Currency;
use Espo\Core\Utils\Config;
use Espo\ORM\BaseEntity;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Entity;

class CurrencyType extends FloatType
{
    private const DEFAULT_PRECISION = 13;

    public function __construct(private Config $config) {}

    protected function isNotEmpty(Entity $entity, string $field): bool
    {
        return
            $entity->has($field) && $entity->get($field) !== null &&
            $entity->has($field . 'Currency') && $entity->get($field . 'Currency') !== null &&
            $entity->get($field . 'Currency') !== '';
    }

    public function checkValid(Entity $entity, string $field): bool
    {
        if (!$this->isNotEmpty($entity, $field)) {
            return true;
        }

        if ($entity->getAttributeType($field) !== Entity::VARCHAR) {
            return true;
        }

        /** @var string $value */
        $value = $entity->get($field);

        if (preg_match('/^-?[0-9]+\.?[0-9]*$/', $value)) {
            return true;
        }

        return false;
    }

    public function checkInPermittedRange(Entity $entity, string $field): bool
    {
        if (!$this->isNotEmpty($entity, $field)) {
            return true;
        }

        if ($entity->getAttributeType($field) !== Entity::VARCHAR) {
            return true;
        }

        if (!$entity instanceof BaseEntity) {
            return true;
        }

        /** @var int $precision */
        $precision = $entity->getAttributeParam($field, AttributeParam::PRECISION) ?? self::DEFAULT_PRECISION;

        $value = $entity->get($field);

        $currency = Currency::create($value, 'USD');

        if ($currency->isNegative()) {
            $currency = $currency->multiply(-1);
        }

        $pad = str_pad('', $precision, '9');

        assert(is_numeric($pad));

        $limit = Currency::create($pad, 'USD');

        if ($currency->compare($limit) === 1) {
            return false;
        }

        return true;
    }

    public function checkValidCurrency(Entity $entity, string $field): bool
    {
        $attribute = $field . 'Currency';

        if (!$entity->has($attribute)) {
            return true;
        }

        $currency = $entity->get($attribute);
        $currencyList = $this->config->get('currencyList') ?? [$this->config->get('defaultCurrency')];

        if (
            $currency === null &&
            !$entity->has($field) &&
            $entity->isNew()
        ) {
            return true;
        }

        if (
            $currency === null &&
            $entity->has($field) &&
            $entity->get($field) === null
        ) {
            return true;
        }

        return in_array($currency, $currencyList);
    }
}
