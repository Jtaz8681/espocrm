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

namespace Espo\Classes\DefaultLayouts;

use Espo\Core\Name\Field;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Team;
use Espo\Entities\User;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\ORM\Defs\Params\RelationParam;
use stdClass;

class DefaultSidePanelType
{
    public function __construct(private Metadata $metadata)
    {}

    /**
     * @return stdClass[]
     */
    public function get(string $scope): array
    {
        $list = [];

        if (
            $this->metadata->get(['entityDefs', $scope, 'fields', Field::ASSIGNED_USER, FieldParam::TYPE]) ===
                FieldType::LINK &&
            $this->metadata->get(['entityDefs', $scope, 'links', Field::ASSIGNED_USER, RelationParam::ENTITY]) ===
                User::ENTITY_TYPE
            ||
            $this->metadata->get(['entityDefs', $scope, 'fields', Field::ASSIGNED_USERS, FieldParam::TYPE]) ===
                FieldType::LINK_MULTIPLE &&
            $this->metadata->get(['entityDefs', $scope, 'links', Field::ASSIGNED_USERS, RelationParam::ENTITY]) ===
                User::ENTITY_TYPE
        ) {
            $list[] = (object) ['name' => ':assignedUser'];
        }

        if (
            $this->metadata->get(['entityDefs', $scope, 'fields', Field::TEAMS, FieldParam::TYPE]) ===
                FieldType::LINK_MULTIPLE &&
            $this->metadata->get(['entityDefs', $scope, 'links', Field::TEAMS, RelationParam::ENTITY]) ===
                Team::ENTITY_TYPE
        ) {
            $list[] = (object) ['name' => Field::TEAMS];
        }

        if (
            $this->metadata->get("entityDefs.$scope.fields.collaborators.type") === FieldType::LINK_MULTIPLE &&
            $this->metadata->get("entityDefs.$scope.links.collaborators.entity") === User::ENTITY_TYPE
        ) {
            $list[] = (object) ['name' => Field::COLLABORATORS];
        }

        return $list;
    }
}
