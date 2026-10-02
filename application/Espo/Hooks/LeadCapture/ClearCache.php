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

namespace Espo\Hooks\LeadCapture;

use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\Utils\DataCache;
use Espo\Entities\LeadCapture;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements AfterSave<LeadCapture>
 */
class ClearCache implements AfterSave
{
    private const CACHE_KEY_PREFIX = 'leadCaptureForm';

    public function __construct(
        private DataCache $dataCache,
    ) {}

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if ($entity->isNew()) {
            return;
        }

        $this->dataCache->clear($this->getCacheKey($entity));
    }

    private function getCacheKey(LeadCapture $leadCapture): string
    {
        return self::CACHE_KEY_PREFIX . '/' . $leadCapture->getId();
    }
}
