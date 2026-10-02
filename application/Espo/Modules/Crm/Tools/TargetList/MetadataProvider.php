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

namespace Espo\Modules\Crm\Tools\TargetList;

use Espo\Core\Utils\Metadata;
use Espo\Modules\Crm\Entities\TargetList;
use Espo\ORM\Defs;

class MetadataProvider
{
    public function __construct(
        private Metadata $metadata,
        private Defs $defs
    ) {}

    /**
     * @return string[]
     */
    public function getTargetLinkList(): array
    {
        return $this->metadata->get(['scopes', 'TargetList', 'targetLinkList']) ?? [];
    }

    /**
     * @return array<string, string>
     */
    public function getEntityTypeLinkMap(): array
    {
        $map = [];

        foreach ($this->getTargetLinkList() as $link) {
            $entityType = $this->defs
                ->getEntity(TargetList::ENTITY_TYPE)
                ->getRelation($link)
                ->getForeignEntityType();

            $map[$entityType] = $link;
        }

        return $map;
    }
}
