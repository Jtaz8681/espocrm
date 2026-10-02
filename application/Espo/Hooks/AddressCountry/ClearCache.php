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

namespace Espo\Hooks\AddressCountry;

use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\Utils\DataCache;
use Espo\Entities\AddressCountry;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements AfterRemove<AddressCountry>
 * @implements AfterSave<AddressCountry>
 */
class ClearCache implements AfterRemove, AfterSave
{
    private const CACHE_KEY = 'addressCountryData';

    public function __construct(
        private DataCache $dataCache,
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $this->dataCache->clear(self::CACHE_KEY);
    }

    public function afterRemove(Entity $entity, RemoveOptions $options): void
    {
        $this->dataCache->clear(self::CACHE_KEY);
    }
}
