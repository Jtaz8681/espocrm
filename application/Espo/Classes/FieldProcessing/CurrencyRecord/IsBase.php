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

namespace Espo\Classes\FieldProcessing\CurrencyRecord;

use Espo\Core\Currency\ConfigDataProvider;
use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Entities\CurrencyRecord;
use Espo\ORM\Entity;
use ValueError;

/**
 * @implements Loader<CurrencyRecord>
 */
class IsBase implements Loader
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
    ) {}


    public function process(Entity $entity, Params $params): void
    {
        try {
            $code = $entity->getCode();
        } catch (ValueError) {
            $entity->setIsBase(false);

            return;
        }

        $isBase = $code === $this->configDataProvider->getBaseCurrency();

        $entity->setIsBase($isBase);
    }
}
