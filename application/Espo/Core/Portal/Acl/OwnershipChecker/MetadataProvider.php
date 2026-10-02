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

namespace Espo\Core\Portal\Acl\OwnershipChecker;

use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs;
use Espo\ORM\Defs\RelationDefs;

class MetadataProvider
{
    public function __construct(
        private Metadata $metadata,
        private Defs $defs,
    ) {}

    public function getAccountLink(string $entityType): ?RelationDefs
    {
        $link = $this->metadata->get("aclDefs.$entityType.accountLink");

        if (!$link) {
            return null;
        }

        return $this->defs->getEntity($entityType)->tryGetRelation($link);
    }

    public function getContactLink(string $entityType): ?RelationDefs
    {
        $link = $this->metadata->get("aclDefs.$entityType.contactLink");

        if (!$link) {
            return null;
        }

        return $this->defs->getEntity($entityType)->tryGetRelation($link);
    }
}
