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

namespace Espo\Core\Acl;

use Espo\Core\Name\Field;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\ORM\Defs;

class OwnerUserFieldProvider
{
    protected const FIELD_ASSIGNED_USERS = Field::ASSIGNED_USERS;
    protected const FIELD_ASSIGNED_USER = Field::ASSIGNED_USER;
    protected const FIELD_CREATED_BY = Field::CREATED_BY;

    public function __construct(private Defs $ormDefs, private Metadata $metadata)
    {}

    /**
     * Get an entity field that stores an owner-user (or multiple users).
     * Must be a link or linkMultiple field. NULL means no owner.
     */
    public function get(string $entityType): ?string
    {
        $value = $this->metadata->get(['aclDefs', $entityType, 'readOwnerUserField']);

        if ($value) {
            return $value;
        }

        $defs = $this->ormDefs->getEntity($entityType);

        if (
            $defs->hasField(self::FIELD_ASSIGNED_USERS) &&
            $defs->getField(self::FIELD_ASSIGNED_USERS)->getType() === FieldType::LINK_MULTIPLE &&
            $defs->hasRelation(self::FIELD_ASSIGNED_USERS) &&
            $defs->getRelation(self::FIELD_ASSIGNED_USERS)->getForeignEntityType() === User::ENTITY_TYPE
        ) {
            return self::FIELD_ASSIGNED_USERS;
        }

        if (
            $defs->hasField(self::FIELD_ASSIGNED_USER) &&
            $defs->getField(self::FIELD_ASSIGNED_USER)->getType() === FieldType::LINK &&
            $defs->hasRelation(self::FIELD_ASSIGNED_USER) &&
            $defs->getRelation(self::FIELD_ASSIGNED_USER)->getForeignEntityType() === User::ENTITY_TYPE
        ) {
            return self::FIELD_ASSIGNED_USER;
        }

        if (
            $defs->hasField(self::FIELD_CREATED_BY) &&
            $defs->getField(self::FIELD_CREATED_BY)->getType() === FieldType::LINK &&
            $defs->hasRelation(self::FIELD_CREATED_BY) &&
            $defs->getRelation(self::FIELD_CREATED_BY)->getForeignEntityType() === User::ENTITY_TYPE
        ) {
            return self::FIELD_CREATED_BY;
        }

        return null;
    }
}
