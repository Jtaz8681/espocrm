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

use Espo\Core\Name\Field;
use Espo\Core\Select\AccessControl\Filter;
use Espo\Core\Select\Helpers\FieldHelper;
use Espo\Entities\Team;
use Espo\Entities\User;
use Espo\ORM\Defs;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\SelectBuilder;

/**
 * @noinspection PhpUnused
 */
class OnlyTeam implements Filter
{
    public function __construct(
        private User $user,
        private FieldHelper $fieldHelper,
        private string $entityType,
        private Defs $defs
    ) {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        if (!$this->fieldHelper->hasTeamsField()) {
            $queryBuilder->where([Attribute::ID => null]);

            return;
        }

        $subQueryBuilder = SelectBuilder::create()
            ->select(Attribute::ID)
            ->from($this->entityType)
            ->leftJoin(Team::RELATIONSHIP_ENTITY_TEAM, 'entityTeam', [
                'entityTeam.entityId:' => Attribute::ID,
                'entityTeam.entityType' => $this->entityType,
                'entityTeam.deleted' => false,
            ]);

        // Empty list is converted to false statement by ORM.
        $orGroup = ['entityTeam.teamId' => $this->user->getTeamIdList()];

        if ($this->fieldHelper->hasAssignedUsersField()) {
            $relationDefs = $this->defs
                ->getEntity($this->entityType)
                ->getRelation(Field::ASSIGNED_USERS);

            $middleEntityType = ucfirst($relationDefs->getRelationshipName());
            $key1 = $relationDefs->getMidKey();
            $key2 = $relationDefs->getForeignMidKey();

            $midConditions = [
                "assignedUsersMiddle.$key1:" => Attribute::ID,
                'assignedUsersMiddle.deleted' => false,
            ];

            foreach ($relationDefs->getConditions() as $key => $value) {
                $midConditions["assignedUsersMiddle.$key"] = $value;
            }

            $subQueryBuilder->leftJoin($middleEntityType, 'assignedUsersMiddle', $midConditions);

            $orGroup["assignedUsersMiddle.$key2"] = $this->user->getId();
        } else if ($this->fieldHelper->hasAssignedUserField()) {
            $orGroup['assignedUserId'] = $this->user->getId();
        } else if ($this->fieldHelper->hasCreatedByField()) {
            $orGroup['createdById'] = $this->user->getId();
        }

        if ($this->fieldHelper->hasCollaboratorsField()) {
            $relationDefs = $this->defs
                ->getEntity($this->entityType)
                ->getRelation(Field::COLLABORATORS);

            $middleEntityType = ucfirst($relationDefs->getRelationshipName());
            $key1 = $relationDefs->getMidKey();
            $key2 = $relationDefs->getForeignMidKey();

            $midConditions = [
                "collaboratorsMiddle.$key1:" => Attribute::ID,
                'collaboratorsMiddle.deleted' => false,
            ];

            foreach ($relationDefs->getConditions() as $key => $value) {
                $midConditions["collaboratorsMiddle.$key"] = $value;
            }

            $subQueryBuilder->leftJoin($middleEntityType, 'collaboratorsMiddle', $midConditions);

            $orGroup["collaboratorsMiddle.$key2"] = $this->user->getId();
        }

        $subQuery = $subQueryBuilder
            ->where(['OR' => $orGroup])
            ->build();

        $queryBuilder->where(['id=s' => $subQuery]);
    }
}
