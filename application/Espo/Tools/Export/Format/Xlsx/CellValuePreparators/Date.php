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

namespace Espo\Tools\Export\Format\Xlsx\CellValuePreparators;

use Espo\Core\Field\Date as DateValue;
use Espo\ORM\Entity;
use Espo\Tools\Export\Format\CellValuePreparator;

class Date implements CellValuePreparator
{
    public function prepare(Entity $entity, string $name): ?DateValue
    {
        $value = $entity->get($name);

        if (!$value) {
            return null;
        }

        return DateValue::fromString($value);
    }
}
