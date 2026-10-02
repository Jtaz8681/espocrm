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

namespace Espo\Modules\Crm\Classes\RecordHooks\Call;

use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Record\ServiceContainer;
use Espo\Modules\Crm\Entities\Call;
use Espo\ORM\Entity;

/**
 * @implements SaveHook<Call>
 */
class AfterUpdate implements SaveHook
{
    public function __construct(
        private ServiceContainer $serviceContainer
    ) {}

    public function process(Entity $entity): void
    {
        if (
            !$entity->isAttributeChanged('contactsIds') &&
            !$entity->isAttributeChanged('leadsIds')
        ) {
            return;
        }

        $this->serviceContainer->getByClass(Call::class)->loadAdditionalFields($entity);
    }
}
