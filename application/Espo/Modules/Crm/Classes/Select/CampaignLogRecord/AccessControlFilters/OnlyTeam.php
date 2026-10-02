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

namespace Espo\Modules\Crm\Classes\Select\CampaignLogRecord\AccessControlFilters;

use Espo\Core\Name\Field;
use Espo\Core\Select\AccessControl\Filter;
use Espo\ORM\Query\SelectBuilder;

use Espo\Entities\User;

class OnlyTeam implements Filter
{
    public function __construct(private User $user)
    {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        $queryBuilder->leftJoin('campaign', 'campaignAccess');

        $teamIdList = $this->user->getLinkMultipleIdList(Field::TEAMS);

        if (count($teamIdList) === 0) {
            $queryBuilder->where([
                'campaignAccess.assignedUserId' => $this->user->getId(),
            ]);

            return;
        }

        $queryBuilder
            ->leftJoin(
                'EntityTeam',
                'entityTeamAccess',
                [
                    'entityTeamAccess.entityType' => 'Campaign',
                    'entityTeamAccess.entityId:' => 'campaignAccess.id',
                    'entityTeamAccess.deleted' => false,
                ]
            )
            ->where([
                'OR' => [
                    'entityTeamAccess.teamId' => $teamIdList,
                    'campaignAccess.assignedUserId' => $this->user->getId(),
                ],
                'campaignId!=' => null,
            ]);
    }
}
