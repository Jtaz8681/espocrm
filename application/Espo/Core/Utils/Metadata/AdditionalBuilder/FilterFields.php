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

namespace Espo\Core\Utils\Metadata\AdditionalBuilder;

use Espo\Core\Utils\Metadata\AdditionalBuilder;
use stdClass;

/**
 * @noinspection PhpUnused
 */
class FilterFields implements AdditionalBuilder
{
    public function __construct()
    {}

    public function build(stdClass $data): void
    {
        if (!isset($data->entityDefs)) {
            return;
        }

        foreach (get_object_vars($data->entityDefs) as $entityDefsItem) {
            if (isset($entityDefsItem->fields) && is_object($entityDefsItem->fields)) {
                foreach (get_object_vars($entityDefsItem->fields) as $field => $fieldDefsItem) {
                    if (!isset($fieldDefsItem->type)) {
                        unset($entityDefsItem->fields->$field);
                    }
                }
            }

            if (isset($entityDefsItem->links) && is_object($entityDefsItem->links)) {
                foreach (get_object_vars($entityDefsItem->links) as $link => $linkDefsItem) {
                    if (!isset($linkDefsItem->type)) {
                        unset($entityDefsItem->links->$link);
                    }
                }
            }
        }
    }
}
