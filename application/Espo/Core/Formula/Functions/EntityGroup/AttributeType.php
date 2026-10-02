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

namespace Espo\Core\Formula\Functions\EntityGroup;

use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\TooFewArguments;

class AttributeType extends \Espo\Core\Formula\Functions\AttributeType
{
    public function process(\stdClass $item)
    {
        if (count($item->value) < 1) {
            throw TooFewArguments::create(1);
        }

        $attribute = $this->evaluate($item->value[0]);

        if (!is_string($attribute)) {
            throw BadArgumentType::create(1, 'string');
        }

        return $this->getAttributeValue($attribute);
    }
}
