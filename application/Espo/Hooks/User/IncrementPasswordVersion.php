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

namespace Espo\Hooks\User;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;
use RuntimeException;

/**
 * @implements BeforeSave<User>
 */
class IncrementPasswordVersion implements BeforeSave
{
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isAttributeChanged(User::FIELD_PASSWORD)) {
            return;
        }

        $version = $entity->get(User::FIELD_PASSWORD_VERSION) ?? 0;

        if (!is_int($version)) {
            throw new RuntimeException("Non-int passwordVersion.");
        }

        $version ++;

        $entity->set(User::FIELD_PASSWORD_VERSION, $version);
    }
}
