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

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements AfterSave<Opportunity>
 */
class AmountWeightedConverted implements AfterSave
{
    /**
     * @param Opportunity $entity
     */
    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if (
            !$entity->isAttributeChanged('amount') &&
            !$entity->isAttributeChanged('probability')
        ) {
            return;
        }

        $amountConverted = $entity->get('amountConverted');
        $probability = $entity->get('probability');

        $amountWeightedConverted = round($amountConverted * $probability / 100, 2);

        $entity->set('amountWeightedConverted', $amountWeightedConverted);
    }
}
