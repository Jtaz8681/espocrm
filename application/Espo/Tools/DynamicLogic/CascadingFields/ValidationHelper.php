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

namespace Espo\Tools\DynamicLogic\CascadingFields;

use Espo\Core\FieldValidation\Validator\Failure;
use Espo\Core\ORM\Entity as CoreEntity;
use Espo\Core\ORM\Type\FieldType;
use Espo\ORM\Defs;

/**
 * @internal
 */
class ValidationHelper
{
    public function __construct(
        private Defs $defs,
    ) {}

    public function validateItem(CoreEntity $entity, CoreEntity $valueEntity, Item $item): ?Failure
    {
        if (!$item->matchRequired) {
            return null;
        }

        $localIds = $this->getIds($entity, $item->localField);
        $foreignIds = $this->getIds($valueEntity, $item->foreignField) ?? [];

        if (!$localIds) {
            if ($foreignIds) {
                return Failure::create();
            }

            return null;
        }

        if ($this->getType($valueEntity, $item->foreignField) === FieldType::LINK_MULTIPLE) {
            if (!array_diff($localIds, $foreignIds)) {
                return null;
            }

            return Failure::create();
        }

        if (array_intersect($localIds, $foreignIds)) {
            return null;
        }

        return Failure::create();
    }

    /**
     * @return ?string[]
     */
    private function getIds(CoreEntity $entity, string $field): ?array
    {
        $type = $this->getType($entity, $field);

        if ($type === FieldType::LINK_MULTIPLE) {
            return $entity->getLinkMultipleIdList($field);
        }

        $localId = $entity->get($field . 'Id');

        if (!$localId) {
            return null;
        }

        return [$localId];
    }


    private function getType(CoreEntity $entity, string $field): ?string
    {
        return $this->defs
            ->getEntity($entity->getEntityType())
            ->tryGetField($field)
            ?->getType();
    }
}
