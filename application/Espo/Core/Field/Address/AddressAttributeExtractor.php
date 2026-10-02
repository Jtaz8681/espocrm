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

namespace Espo\Core\Field\Address;

use Espo\ORM\Value\AttributeExtractor;

use Espo\Core\Field\Address;

use stdClass;
use InvalidArgumentException;

/**
 * @implements AttributeExtractor<Address>
 */
class AddressAttributeExtractor implements AttributeExtractor
{
    public function extract(object $value, string $field): stdClass
    {
        if (!$value instanceof Address) {
            throw new InvalidArgumentException();
        }

        return (object) [
            $field . 'Street' => $value->getStreet(),
            $field . 'City' => $value->getCity(),
            $field . 'Country' => $value->getCountry(),
            $field . 'State' => $value->getState(),
            $field . 'PostalCode' => $value->getPostalCode(),
        ];
    }

    public function extractFromNull(string $field): stdClass
    {
        return (object) [
            $field . 'Street' => null,
            $field . 'City' => null,
            $field . 'Country' => null,
            $field . 'State' => null,
            $field . 'PostalCode' => null,
        ];
    }
}
