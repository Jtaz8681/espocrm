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

namespace Espo\Modules\Crm\Hooks\Lead;

use Espo\Core\Field\DateTime;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Modules\Crm\Entities\Lead;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements BeforeSave<Lead>
 */
class ConvertedAt implements BeforeSave
{
    /**
     * @param Lead $entity
     */
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (!$entity->isAttributeChanged('status')) {
            return;
        }

        if ($entity->getConvertedAt() ) {
            return;
        }

        if ($entity->getStatus() !== Lead::STATUS_CONVERTED) {
            return;
        }

        $entity->setValueObject('convertedAt', DateTime::createNow());
    }
}
