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

namespace Espo\Modules\Crm\Classes\RecordHooks\Lead;

use Espo\Core\Acl;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Utils\DateTime;
use Espo\Entities\Email;
use Espo\Modules\Crm\Entities\Campaign as Campaign;
use Espo\Modules\Crm\Entities\CampaignLogRecord as CampaignLogRecord;
use Espo\Modules\Crm\Entities\Lead;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @implements SaveHook<Lead>
 */
class AfterCreate implements SaveHook
{
    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl
    ) {}

    public function process(Entity $entity): void
    {
        $this->processOriginalEmail($entity);
        $this->processCampaignLog($entity);
    }

    private function processOriginalEmail(Lead $entity): void
    {
        $emailId = $entity->get('originalEmailId');

        if (!$emailId) {
            return;
        }

        /** @var ?Email $email */
        $email = $this->entityManager->getEntityById(Email::ENTITY_TYPE, $emailId);

        if (!$email || $email->getParentId() || !$this->acl->check($email)) {
            return;
        }

        $email->set([
            'parentType' => Lead::ENTITY_TYPE,
            'parentId' => $entity->getId(),
        ]);

        $this->entityManager->saveEntity($email);
    }

    private function processCampaignLog(Lead $entity): void
    {
        $campaign = $entity->getCampaign();

        if (!$campaign) {
            return;
        }

        $log = $this->entityManager->getNewEntity(CampaignLogRecord::ENTITY_TYPE);

        $log->set([
            'action' => CampaignLogRecord::ACTION_LEAD_CREATED,
            'actionDate' => DateTime::getSystemNowString(),
            'parentType' => Lead::ENTITY_TYPE,
            'parentId' => $entity->getId(),
            'campaignId' => $campaign->getId(),
        ]);

        $this->entityManager->saveEntity($log);
    }
}
