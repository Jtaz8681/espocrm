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

namespace Espo\Modules\Crm\Hooks\CaseObj;

use Espo\Core\FieldProcessing\Stream\FollowersLoader;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\Tools\Stream\Service;


/**
 * @implements AfterSave<CaseObj>
 */
class IsInternal implements AfterSave
{
    public function __construct(
        private Service $streamService,
        private FollowersLoader $followersLoader,
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $this->processUnfollowPortalUsers($entity);
    }

    private function processUnfollowPortalUsers(CaseObj $entity): void
    {
        if (!$entity->isInternal() || $entity->isNew()) {
            return;
        }

        $this->streamService->unfollowPortalUsersFromEntity($entity);

        $this->followersLoader->processFollowers($entity);
    }
}
