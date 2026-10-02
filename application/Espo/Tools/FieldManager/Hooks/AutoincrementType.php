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

namespace Espo\Tools\FieldManager\Hooks;

use Espo\Core\Di;
use Espo\Core\Exceptions\Error;
use Espo\Core\ORM\Type\FieldType;

class AutoincrementType implements Di\MetadataAware
{
    use Di\MetadataSetter;

    /**
     * @param array<string, mixed> $defs
     * @param array<string, mixed> $options
     * @throws Error
     */
    public function beforeSave(string $scope, string $name, $defs, $options): void
    {
        if (!isset($options['isNew']) || !$options['isNew']) {
            return;
        }

        $fields = $this->metadata->get(['entityDefs', $scope, 'fields']);

        foreach ($fields as $fieldDefs) {
            if ($fieldDefs['type'] == FieldType::AUTOINCREMENT) {
                throw new Error('The entity can have only one Auto-increment field.');
            }
        }
    }
}
