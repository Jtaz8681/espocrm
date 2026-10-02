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

namespace Espo\Core\Field\Currency;

use Espo\ORM\Defs;
use Espo\ORM\Entity;
use Espo\ORM\Value\AttributeExtractor;

use Espo\Core\Field\Currency;

use stdClass;
use InvalidArgumentException;

/**
 * @implements AttributeExtractor<Currency>
 */
class CurrencyAttributeExtractor implements AttributeExtractor
{
    public function __construct(
        private string $entityType,
        private Defs $ormDefs
    ) {}

    public function extract(object $value, string $field): stdClass
    {
        if (!$value instanceof Currency) {
            throw new InvalidArgumentException();
        }

        $useString = $this->ormDefs
            ->getEntity($this->entityType)
            ->getField($field)
            ->getType() === Entity::VARCHAR;

        $amount = $useString ?
            $value->getAmountAsString() :
            $value->getAmount();

        return (object) [
            $field => $amount,
            $field . 'Currency' => $value->getCode(),
        ];
    }

    public function extractFromNull(string $field): stdClass
    {
        return (object) [
            $field => null,
            $field . 'Currency' => null,
        ];
    }
}
