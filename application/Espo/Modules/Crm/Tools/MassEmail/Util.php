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

namespace Espo\Modules\Crm\Tools\MassEmail;

use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs;
use Espo\Modules\Crm\Entities\TargetList;
use RuntimeException;

class Util
{
    /** @var string[] */
    private array $targetLinkList;

    public function __construct(
        private Defs $ormDefs,
        private Metadata $metadata
    ) {
        $this->targetLinkList = $this->metadata->get(['scopes', 'TargetList', 'targetLinkList']) ?? [];
    }

    public function getLinkByEntityType(string $entityType): string
    {
        foreach ($this->targetLinkList as $link) {
            $itemEntityType = $this->ormDefs
                ->getEntity(TargetList::ENTITY_TYPE)
                ->getRelation($link)
                ->getForeignEntityType();

            if ($itemEntityType === $entityType) {
                return $link;
            }
        }

        throw new RuntimeException("No link for $entityType.");
    }
}
