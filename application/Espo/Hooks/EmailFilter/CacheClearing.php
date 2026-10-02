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

namespace Espo\Hooks\EmailFilter;

use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\Utils\DataCache;
use Espo\Entities\EmailFilter;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements AfterSave<EmailFilter>
 * @implements AfterRemove<EmailFilter>
 */
class CacheClearing implements AfterSave, AfterRemove
{
    private const CACHE_KEY = 'emailFilters';

    public function __construct(private DataCache $dataCache) {}

    /**
     * @param EmailFilter $entity
     */
    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        $this->processEntity($entity);
    }

    /**
     * @param EmailFilter $entity
     */
    public function afterRemove(Entity $entity, RemoveOptions $options): void
    {
        $this->processEntity($entity);
    }

    private function processEntity(EmailFilter $entity): void
    {
        if ($entity->getParentType() !== User::ENTITY_TYPE || !$entity->getParentId()) {
            return;
        }

        $cacheKey = $this->composeCacheKey($entity->getParentId());

        $this->dataCache->clear($cacheKey);
    }

    private function composeCacheKey(string $userId): string
    {
        return self::CACHE_KEY . '/' . $userId;
    }
}
