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

namespace Espo\Classes\FieldValidators\Email;

use Espo\Entities\Email;
use Espo\ORM\Entity;

class EmailAddresses
{
    /**
     * @param Email $entity
     */
    public function checkRequired(Entity $entity, string $field): bool
    {
        if ($entity->getStatus() === Email::STATUS_DRAFT) {
            return true;
        }

        return $this->isNotEmpty($entity, $field);
    }

    private function isNotEmpty(Entity $entity, string $field): bool
    {
        return $entity->has($field) && $entity->get($field) !== '' && $entity->get($field) !== null;
    }
}
