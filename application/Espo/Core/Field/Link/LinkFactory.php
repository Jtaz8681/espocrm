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

namespace Espo\Core\Field\Link;

use Espo\ORM\Entity;
use Espo\ORM\Value\ValueFactory;
use Espo\Core\Field\Link;

use RuntimeException;

class LinkFactory implements ValueFactory
{
    public function isCreatableFromEntity(Entity $entity, string $field): bool
    {
        return $entity->get($field . 'Id') !== null;
    }

    public function createFromEntity(Entity $entity, string $field): Link
    {
        if (!$this->isCreatableFromEntity($entity, $field)) {
            throw new RuntimeException();
        }

        $id = $entity->get($field . 'Id');
        $name = $entity->get($field . 'Name');

        return Link
            ::create($id)
            ->withName($name);
    }
}
