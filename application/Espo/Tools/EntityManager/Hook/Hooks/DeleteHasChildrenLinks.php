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

namespace Espo\Tools\EntityManager\Hook\Hooks;

use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Type\RelationType;
use Espo\Tools\EntityManager\Hook\DeleteHook;
use Espo\Tools\EntityManager\Params;

/**
 * @noinspection PhpUnused
 */
class DeleteHasChildrenLinks implements DeleteHook
{
    public function __construct(
        private Metadata $metadata,
    ) {}

    public function process(Params $params): void
    {
        /** @var array<string, array<string, mixed>> $entityDefs */
        $entityDefs = $this->metadata->get('entityDefs', []);

        foreach ($entityDefs as $entityType => $defs) {
            /** @var array<string, array<string, mixed>> $links */
            $links = $defs['links'] ?? [];

            foreach ($links as $link => $linkDefs) {
                $isCustom = $linkDefs['isCustom'] ?? false;
                $foreignEntityType = $linkDefs[RelationParam::ENTITY] ?? null;
                $type = $linkDefs[RelationParam::TYPE] ?? null;

                if (
                    !$isCustom ||
                    $foreignEntityType !== $params->getName() ||
                    $type !== RelationType::HAS_CHILDREN
                ) {
                    continue;
                }

                $this->metadata->delete('entityDefs', $entityType, "links.$link");
            }
        }

        $this->metadata->save();
    }
}
