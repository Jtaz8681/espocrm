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

namespace Espo\Hooks\Common;

use Espo\Core\ORM\Type\FieldType;
use Espo\ORM\Entity;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\FieldUtil;

class CurrencyDefault
{
    public static int $order = 200;

    public function __construct(private Config $config, private FieldUtil $fieldUtil)
    {}

    public function beforeSave(Entity $entity): void
    {
        $fieldList = $this->fieldUtil->getFieldByTypeList($entity->getEntityType(), FieldType::CURRENCY);

        $defaultCurrency = $this->config->get('defaultCurrency');

        foreach ($fieldList as $field) {
            $currencyAttribute = $field . 'Currency';

            if ($entity->isNew()) {
                if ($entity->get($field) && !$entity->get($currencyAttribute)) {
                    $entity->set($currencyAttribute, $defaultCurrency);
                }

                continue;
            }

            if (
                $entity->isAttributeChanged($field) && $entity->has($currencyAttribute) &&
                !$entity->get($currencyAttribute)
            ) {
                $entity->set($currencyAttribute, $defaultCurrency);
            }
        }
    }
}
