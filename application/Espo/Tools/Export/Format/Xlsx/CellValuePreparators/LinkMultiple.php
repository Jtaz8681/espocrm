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

use Espo\ORM\Entity;
use Espo\Tools\Export\Format\CellValuePreparator;
use stdClass;

class LinkMultiple implements CellValuePreparator
{
    public function prepare(Entity $entity, string $name): ?string
    {
        if (
            !$entity->has($name . 'Ids') ||
            !$entity->has($name . 'Names')
        ) {
            return null;
        }

        /** @var string[] $ids */
        $ids = $entity->get($name . 'Ids');
        /** @var ?stdClass $names */
        $names = $entity->get($name . 'Names');

        $nameList = array_map(function ($id) use ($names) {
            return $names->$id ?? $id;
        }, $ids);

        return implode(',', $nameList);
    }
}
