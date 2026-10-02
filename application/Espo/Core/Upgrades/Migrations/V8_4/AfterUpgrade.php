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

namespace Espo\Core\Upgrades\Migrations\V8_4;

use Espo\Core\Upgrades\Migration\Script;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Type\RelationType;

class AfterUpgrade implements Script
{
    public function __construct(
        private Metadata $metadata,
    ) {}

    public function run(): void
    {
        $this->updateMetadata();
    }

    private function updateMetadata(): void
    {
        $defs = $this->metadata->get(['entityDefs']);

        $toSave = false;

        foreach ($defs as $entityType => $item) {
            if (!isset($item['links'])) {
                continue;
            }

            foreach ($item['links'] as $link => $linkDefs) {
                $type = $linkDefs['type'] ?? null;
                $foreignEntityType = $linkDefs['entity'] ?? null;
                $midKeys = $linkDefs[RelationParam::MID_KEYS] ?? null;
                $isCustom = $linkDefs['isCustom'] ?? false;

                if ($type !== RelationType::HAS_MANY) {
                    continue;
                }

                if ($foreignEntityType !== $entityType) {
                    continue;
                }

                if (!$midKeys) {
                    continue;
                }

                if (!$isCustom) {
                    continue;
                }

                if ($linkDefs['_keysSwappedAfterUpgrade'] ?? false) {
                    continue;
                }

                $this->metadata->set('entityDefs', $entityType, [
                    'links' => [
                        $link => [
                            RelationParam::MID_KEYS => array_reverse($midKeys),
                            '_keysSwappedAfterUpgrade' => true,
                        ]
                    ]
                ]);

                $toSave = true;
            }

            if ($toSave) {
                $this->metadata->save();
            }
        }
    }
}
