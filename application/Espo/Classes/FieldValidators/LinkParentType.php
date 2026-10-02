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

use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs;
use Espo\ORM\Entity;

class LinkParentType
{
    private Metadata $metadata;
    private Defs $defs;

    public function __construct(Metadata $metadata, Defs $defs)
    {
        $this->metadata = $metadata;
        $this->defs = $defs;
    }

    public function checkRequired(Entity $entity, string $field): bool
    {
        $idAttribute = $field . 'Id';
        $typeAttribute = $field . 'Type';

        if (
            !$entity->has($idAttribute) ||
            $entity->get($idAttribute) === '' ||
            $entity->get($idAttribute) === null
        ) {
            return false;
        }

        if (!$entity->get($typeAttribute)) {
            return false;
        }

        return true;
    }

    public function checkPattern(Entity $entity, string $field): bool
    {
        /** @var ?string $idValue */
        $idValue = $entity->get($field . 'Id');

        if ($idValue === null) {
            return true;
        }

        $pattern = $this->metadata->get(['app', 'regExpPatterns', 'id', 'pattern']);

        if (!$pattern) {
            return true;
        }

        $preparedPattern = '/^' . $pattern . '$/';

        return (bool) preg_match($preparedPattern, $idValue);
    }

    public function checkValid(Entity $entity, string $field): bool
    {
        /** @var ?string $typeValue */
        $typeValue = $entity->get($field . 'Type');

        if ($typeValue === null) {
            return true;
        }

        /** @var ?string[] $entityTypeList */
        $entityTypeList = $this->defs
            ->getEntity($entity->getEntityType())
            ->getField($field)
            ->getParam('entityList');

        if ($entityTypeList !== null) {
            return in_array($typeValue, $entityTypeList);
        }

        return (bool) $this->metadata->get(['entityDefs', $typeValue]);
    }
}
