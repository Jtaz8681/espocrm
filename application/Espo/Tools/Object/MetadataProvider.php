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

namespace Espo\Tools\Object;

use Espo\Core\Name\Field;
use Espo\Core\Utils\Metadata;
use Espo\Modules\Crm\Entities\Account;
use Espo\ORM\Defs;
use Espo\ORM\Type\RelationType;

/**
 * @since 10.0.0
 */
class MetadataProvider
{
    public function __construct(
        private Metadata $metadata,
        private Defs $defs,
    ) {}

    public function getAccountLink(string $entityType): ?string
    {
        $link = $this->metadata->get("scopes.$entityType.accountLink");

        if ($link) {
            return $link;
        }

        $link = Field::ACCOUNT;

        $relationDefs = $this->defs
            ->getEntity($entityType)
            ->tryGetRelation($link);

        if (!$relationDefs) {
            return null;
        }

        if (!in_array($relationDefs->getType(), [RelationType::BELONGS_TO, RelationType::HAS_ONE])) {
            return null;
        }

        if ($relationDefs->tryGetForeignEntityType() !== Account::ENTITY_TYPE) {
            return null;
        }

        return $link;
    }

    public function getParentLink(string $entityType): ?string
    {
        $link = $this->metadata->get("scopes.$entityType.parentLink");

        if ($link) {
            return $link;
        }

        $link = Field::PARENT;

        $relationDefs = $this->defs
            ->getEntity($entityType)
            ->tryGetRelation($link);

        if ($relationDefs?->getType() !== RelationType::BELONGS_TO_PARENT) {
            return null;
        }

        return $link;
    }
}
