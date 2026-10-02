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

namespace Espo\Tools\Pipeline;

use Espo\Core\Acl;
use Espo\Entities\User;
use Espo\Tools\Pipeline\Data\PipelineData;

class PipelineDataUserFilter
{
    public function __construct(
        private Acl $acl,
        private User $user,
    ) {}

    /**
     * @param array<string, PipelineData[]> $data
     * @return array<string, PipelineData[]>
     */
    public function filter(array $data): array
    {
        if ($this->user->isAdmin()) {
            return $data;
        }

        foreach ($data as $entityType => $pipelines) {
            if (!$this->acl->checkScope($entityType)) {
                unset($data[$entityType]);

                continue;
            }

            $filtered = array_filter($pipelines, function ($pipelineData) {
                if ($pipelineData->isAvailableForAll) {
                    return true;
                }

                if ($this->user->isPortal()) {
                    return false;
                }

                return array_intersect($pipelineData->teamIds, $this->user->getTeamIdList()) !== [];
            });

            $filtered = array_values($filtered);

            $data[$entityType] = $filtered;
        }

        return $data;
    }
}
