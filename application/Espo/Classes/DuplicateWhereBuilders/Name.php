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

namespace Espo\Classes\DuplicateWhereBuilders;

use Espo\Core\Duplicate\WhereBuilder;

use Espo\Core\Name\Field;
use Espo\ORM\Entity;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\WhereItem;

/**
 * @implements WhereBuilder<Entity>
 */
class Name implements WhereBuilder
{
    public function build(Entity $entity): ?WhereItem
    {
        if ($entity->get(Field::NAME)) {
            return Cond::equal(
                Cond::column(Field::NAME),
                $entity->get(Field::NAME)
            );
        }

        return null;
    }
}
