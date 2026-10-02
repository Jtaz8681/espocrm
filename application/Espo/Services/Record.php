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

namespace Espo\Services;

use Espo\ORM\Entity;
use Espo\Core\Record\Service as RecordService;
use Espo\Core\Utils\Util;

/**
 * Extending is not recommended. Use composition with metadata > recordDefs.
 *
 * @template TEntity of Entity
 * @extends RecordService<TEntity>
 */
class Record extends RecordService
{
    /**
     * @internal
     */
    protected function initEntityType(): void
    {
        if ($this->entityType) {
            return;
        }

        // Detecting the entity type by the class-name.
        $name = get_class($this);

        $matches = null;

        if (preg_match('@\\\\([\w]+)$@', $name, $matches)) {
            $name = $matches[1];
        }

        $this->entityType = Util::normalizeScopeName($name);
    }
}
