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

namespace Espo\Modules\Crm\Classes\RecordHooks\Opportunity;

use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Utils\Metadata;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\ORM\Entity;

/**
 * @implements SaveHook<Opportunity>
 */
class BeforeUpdate implements SaveHook
{
    public function __construct(
        private Metadata $metadata
    ) {}

    public function process(Entity $entity): void
    {
        $this->setProbability($entity);
    }

    private function setProbability(Opportunity $entity): void
    {
        if ($entity->isAttributeWritten('probability') && $entity->getProbability() !== null) {
            return;
        }

        $stage = $entity->getStage();

        $probability = $this->metadata->get("entityDefs.Opportunity.fields.stage.probabilityMap.$stage");

        if ($probability === null) {
            return;
        }

        $entity->setProbability($probability);
    }
}
