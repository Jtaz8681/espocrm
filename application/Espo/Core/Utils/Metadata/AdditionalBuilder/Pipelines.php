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
class Pipelines implements AdditionalBuilder
{
    public function build(stdClass $data): void
    {
        $scopes = $data->scopes ?? null;

        if (!$scopes instanceof stdClass) {
            return;
        }

        foreach (get_object_vars($scopes) as $scope => $itemDefs) {
            if (
                !($itemDefs->entity ?? false) ||
                !($itemDefs->pipelines ?? false)
            ) {
                continue;
            }

            $statusField = $itemDefs->statusField ?? null;

            if (!$statusField) {
                continue;
            }

            $entityDefs = $data->entityDefs->$scope ?? null;

            if (!$entityDefs instanceof stdClass) {
                return;
            }

            $fieldsDefs = $entityDefs->fields ?? null;

            if (!$fieldsDefs instanceof stdClass) {
                return;
            }

            $statusFieldDefs = $fieldsDefs->$statusField ?? null;

            if (!$statusFieldDefs instanceof stdClass) {
                return;
            }

            $statusFieldDefs->readOnly = true;
        }
    }
}
