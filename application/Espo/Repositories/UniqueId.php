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

namespace Espo\Repositories;

use Espo\Core\Name\Field;
use Espo\ORM\Entity;

use Espo\Core\Utils\Util;
use Espo\Core\Repositories\Database;

/**
 * @extends Database<\Espo\Entities\UniqueId>
 */
class UniqueId extends Database
{
    public function getNew(): Entity
    {
        $entity = parent::getNew();

        $entity->set(Field::NAME, Util::generateMoreEntropyId());

        return $entity;
    }
}
