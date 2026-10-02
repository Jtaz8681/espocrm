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

namespace Espo\Core\Select\AccessControl\Filters;

use Espo\Core\Select\AccessControl\Filter;
use Espo\Core\Select\Helpers\RelationQueryHelper;
use Espo\Core\Select\Helpers\FieldHelper;
use Espo\Entities\User;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Where\OrGroup;
use Espo\ORM\Query\Part\WhereClause;
use Espo\ORM\Query\Part\WhereItem;
use Espo\ORM\Query\SelectBuilder;

class OnlyOwn implements Filter
{
    public function __construct(
        private User $user,
        private FieldHelper $fieldHelper,
        private string $entityType,
        private RelationQueryHelper $relationQueryHelper,
    ) {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        $ownItem = $this->getOwnWhereItem();

        if ($this->fieldHelper->hasCollaboratorsField()) {
            $this->applyCollaborators($queryBuilder, $ownItem);

            return;
        }

        if (!$ownItem) {
            $queryBuilder->where([Attribute::ID => null]);

            return;
        }

        $queryBuilder->where($ownItem);
    }

    private function applyCollaborators(SelectBuilder $queryBuilder, ?WhereItem $ownItem): void
    {
        $sharedItem = $this->relationQueryHelper->prepareCollaboratorsWhere($this->entityType, $this->user->getId());

        $orBuilder = OrGroup::createBuilder();

        if ($ownItem) {
            $orBuilder->add($ownItem);
        }

        $orBuilder->add($sharedItem);

        $queryBuilder->where($orBuilder->build());
    }

    private function getOwnWhereItem(): ?WhereItem
    {
        if ($this->fieldHelper->hasAssignedUsersField()) {
            return $this->relationQueryHelper->prepareAssignedUsersWhere($this->entityType, $this->user->getId());
        }

        if ($this->fieldHelper->hasAssignedUserField()) {
            return WhereClause::fromRaw(['assignedUserId' => $this->user->getId()]);
        }

        if ($this->fieldHelper->hasCreatedByField()) {
            return WhereClause::fromRaw(['createdById' => $this->user->getId()]);
        }

        return null;
    }
}
