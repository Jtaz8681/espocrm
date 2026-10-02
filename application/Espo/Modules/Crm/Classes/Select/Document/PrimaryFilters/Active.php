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

namespace Espo\Modules\Crm\Classes\Select\Document\PrimaryFilters;

use Espo\Core\Select\Primary\Filter;
use Espo\Core\Utils\Metadata;
use Espo\Modules\Crm\Entities\Document;
use Espo\ORM\Query\SelectBuilder;

class Active implements Filter
{
    public function __construct(private Metadata $metadata) {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        $statusList = $this->metadata->get("entityDefs.Document.fields.status.activeOptions") ??
            [Document::STATUS_ACTIVE];

        $queryBuilder->where(['status' => $statusList]);
    }
}
