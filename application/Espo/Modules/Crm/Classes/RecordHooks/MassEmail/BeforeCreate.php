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

namespace Espo\Modules\Crm\Classes\RecordHooks\MassEmail;

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Modules\Crm\Entities\Campaign;
use Espo\Modules\Crm\Entities\MassEmail;
use Espo\ORM\Entity;

/**
 * @implements SaveHook<MassEmail>
 */
class BeforeCreate implements SaveHook
{
    public function __construct(
        private Acl $acl
    ) {}

    public function process(Entity $entity): void
    {
        if (!$this->acl->check($entity, Table::ACTION_EDIT)) {
            throw new Forbidden("No 'edit' access.");
        }

        $this->checkCampaign($entity);
    }

    /**
     * @throws Forbidden
     */
    private function checkCampaign(MassEmail $entity): void
    {
        if (
            !$entity->getCampaign() || in_array($entity->getCampaign()->getType(), [
                Campaign::TYPE_EMAIL,
                Campaign::TYPE_NEWSLETTER,
                Campaign::TYPE_INFORMATIONAL_EMAIL,
            ])
        ) {
            return;
        }

        throw new Forbidden("Cannot create mass email for non-email campaign.");
    }
}
