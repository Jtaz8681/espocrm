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

use Espo\Core\Utils\Metadata;

class ItemsProvider
{
    public function __construct(
        private Metadata $metadata,
    ) {}

    /**
     * @return Item[]
     */
    public function get(string $entityType, string $field): array
    {
        /** @var array<string, mixed>[] $rawItems */
        $rawItems = $this->metadata->get("logicDefs.$entityType.cascadingFields.$field.items") ?? [];

        $items = [];

        foreach ($rawItems as $raw) {
            $localField = $raw['localField'] ?? null;
            $foreignField = $raw['foreignField'] ?? null;
            $matchRequired = $raw['matchRequired'] ?? false;

            if (!$localField || !$foreignField) {
                continue;
            }

            $items[] = new Item(
                localField: $localField,
                foreignField: $foreignField,
                matchRequired: $matchRequired,
            );
        }

        return $items;
    }
}
