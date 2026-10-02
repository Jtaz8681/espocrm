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

namespace Espo\Modules\Crm\Classes\Acl\CampaignLogRecord;

use Espo\Entities\User;
use Espo\Modules\Crm\Entities\CampaignLogRecord;
use Espo\ORM\Entity;
use Espo\Core\Acl\OwnershipOwnChecker;
use Espo\Core\Acl\OwnershipTeamChecker;
use Espo\Core\AclManager;
use Espo\Core\ORM\EntityManager;

/**
 * @implements OwnershipOwnChecker<CampaignLogRecord>
 * @implements OwnershipTeamChecker<CampaignLogRecord>
 */
class OwnershipChecker implements OwnershipOwnChecker, OwnershipTeamChecker
{
    public function __construct(private AclManager $aclManager, private EntityManager $entityManager)
    {}

    public function checkOwn(User $user, Entity $entity): bool
    {
        $campaignId = $entity->get('campaignId');

        if (!$campaignId) {
            return false;
        }

        $campaign = $this->entityManager->getEntityById('Campaign', $campaignId);

        if ($campaign && $this->aclManager->checkOwnershipOwn($user, $campaign)) {
            return true;
        }

        return false;
    }

    public function checkTeam(User $user, Entity $entity): bool
    {
        $campaignId = $entity->get('campaignId');

        if (!$campaignId) {
            return false;
        }

        $campaign = $this->entityManager->getEntityById('Campaign', $campaignId);

        if ($campaign && $this->aclManager->checkOwnershipTeam($user, $campaign)) {
            return true;
        }

        return false;
    }
}
