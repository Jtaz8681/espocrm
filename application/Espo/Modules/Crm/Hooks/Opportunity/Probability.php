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

namespace Espo\Modules\Crm\Hooks\Opportunity;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Utils\Metadata;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements BeforeSave<Opportunity>
 */
class Probability implements BeforeSave
{
    public static int $order = 7;

    public function __construct(private Metadata $metadata) {}

    /**
     * @param Opportunity $entity
     */
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isNew()) {
            return;
        }

        if ($entity->getProbability() !== null) {
            return;
        }

        $stage = $entity->getStage();

        if (!$stage) {
            return;
        }

        $probability = $this->metadata->get("entityDefs.Opportunity.fields.stage.probabilityMap.$stage") ?? 0;

        $entity->setProbability($probability);
    }
}
