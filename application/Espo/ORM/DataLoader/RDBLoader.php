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

namespace Espo\ORM\DataLoader;

use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

class RDBLoader implements Loader
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function load(Entity $entity): void
    {
        $loaded = $this->entityManager->getEntityById($entity->getEntityType(), $entity->getId());

        if (!$loaded) {
            return;
        }

        foreach ($loaded->getAttributeList() as $attribute) {
            if (!$loaded->has($attribute)) {
                continue;
            }

            $value = $loaded->get($attribute);

            if (!$entity->hasFetched($attribute)) {
                $entity->setFetched($attribute, $value);
            }

            if (!$entity->has($attribute)) {
                $entity->set($attribute, $value);
            }
        }
    }
}
