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
use Espo\ORM\Entity;

class LinkType
{
    private Metadata $metadata;

    public function __construct(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

    public function checkRequired(Entity $entity, string $field): bool
    {
        $idAttribute = $field . 'Id';

        if (!$entity->has($idAttribute)) {
            return false;
        }

        return $entity->get($idAttribute) !== null && $entity->get($idAttribute) !== '';
    }

    public function checkPattern(Entity $entity, string $field): bool
    {
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
}
