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

use Espo\Core\Container;
use Espo\Core\Utils\Metadata;

class AfterUpgrade
{
    public function run(Container $container): void
    {
        $this->updateMetadata($container->get('metadata'));
    }

    private function updateMetadata(Metadata $metadata): void
    {
        $this->fixParent($metadata);

        $metadata->save();
    }

    private function fixParent(Metadata $metadata): void
    {
        foreach ($metadata->get(['entityDefs']) as $scope => $defs) {
            foreach ($metadata->get(['entityDefs', $scope, 'fields']) as $field => $fieldDefs) {
                $custom = $metadata->getCustom('entityDefs', $scope);

                if (!$custom) {
                    continue;
                }

                if (
                    ($fieldDefs['type'] ?? null) === 'linkParent' &&
                    ($fieldDefs['notStorable'] ?? false)
                ) {
                    if ($custom?->fields?->$field?->notStorable) {
                        $metadata->delete('entityDefs', $scope, "fields.{$field}.notStorable");
                    }
                }
            }
        }
    }
}
