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

class UrlType
{

    public function __construct(
        private Metadata $metadata,
        private VarcharType $varcharType,
        private Defs $defs,
    ) {}

    public function checkRequired(Entity $entity, string $field): bool
    {
        return $this->varcharType->checkRequired($entity, $field);
    }

    public function checkMaxLength(Entity $entity, string $field, ?int $validationValue): bool
    {
        return $this->varcharType->checkMaxLength($entity, $field, $validationValue);
    }

    public function checkValid(Entity $entity, string $field): bool
    {
        $value = $entity->get($field);

        if ($value === null) {
            return true;
        }

        if (
            $this->defs
                ->getEntity($entity->getEntityType())
                ->tryGetField($field)
                ?->getParam('protocolRequired')
        ) {
            return filter_var($value, FILTER_VALIDATE_URL) !== false;
        }

        /** @var string $pattern */
        $pattern = $this->metadata->get(['app', 'regExpPatterns', 'uriOptionalProtocol', 'pattern']);

        $preparedPattern = '/^' . $pattern . '$/';

        return (bool) preg_match($preparedPattern, $value);
    }
}
