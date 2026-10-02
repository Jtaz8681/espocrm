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

namespace Espo\Hooks\CurrencyRecordRate;

use Espo\Core\Currency\ConfigDataProvider;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Entities\CurrencyRecordRate;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements BeforeSave<CurrencyRecordRate>
 */
class SetFields implements BeforeSave
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
    ) {}


    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if ($entity->isNew()) {
            $baseCode = $this->configDataProvider->getBaseCurrency();

            $entity->setBaseCode($baseCode);
        }
    }
}
