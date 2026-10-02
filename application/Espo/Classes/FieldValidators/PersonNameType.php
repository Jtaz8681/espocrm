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

namespace Espo\Classes\FieldValidators;

use Espo\ORM\Entity;
use Espo\Core\Utils\FieldUtil;

class PersonNameType
{
    public function __construct(private FieldUtil $fieldUtil)
    {}

    public function checkRequired(Entity $entity, string $field): bool
    {
        $isEmpty = true;

        $attributeList = $this->fieldUtil->getActualAttributeList($entity->getEntityType(), $field);

        foreach ($attributeList as $attribute) {
            if ($attribute === 'salutation' . ucfirst($field)) {
                continue;
            }

            if ($entity->has($attribute) && $entity->get($attribute) !== '') {
                $isEmpty = false;

                break;
            }
        }

        if ($isEmpty) {
            return false;
        }

        return true;
    }
}
