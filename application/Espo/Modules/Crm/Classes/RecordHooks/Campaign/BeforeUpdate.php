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

namespace Espo\Modules\Crm\Classes\RecordHooks\Campaign;

use Espo\Core\Exceptions\Error\Body;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Modules\Crm\Entities\Campaign;
use Espo\Modules\Crm\Entities\CampaignTrackingUrl;
use Espo\Modules\Crm\Entities\MassEmail;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @implements SaveHook<Campaign>
 */
class BeforeUpdate implements SaveHook
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function process(Entity $entity): void
    {
        $this->checkType($entity);
    }

    /**
     * @throws Forbidden
     */
    private function checkType(Campaign $entity): void
    {
        if (!$entity->isAttributeChanged('type')) {
            return;
        }

        $massEmail = $this->entityManager
            ->getRDBRepositoryByClass(MassEmail::class)
            ->where(['campaignId' => $entity->getId()])
            ->findOne();


        if ($massEmail) {
            throw Forbidden::createWithBody(
                'Cannot change type.',
                Body::create()->withMessageTranslation('cannotChangeType', Campaign::ENTITY_TYPE)
            );
        }
    }
}
