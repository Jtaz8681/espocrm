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

namespace Espo\Classes\Select\PipelineStage\AccessControlFilters;

use Espo\Core\Select\AccessControl\Filter;
use Espo\Entities\Pipeline;
use Espo\Entities\PipelineStage;
use Espo\Entities\User;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\SelectBuilder;
use Espo\Tools\Pipeline\PipelineDataProvider;
use Espo\Tools\Pipeline\PipelineDataUserFilter;

class Mandatory implements Filter
{
    public function __construct(
        private User $user,
        private PipelineDataProvider $pipelineDataProvider,
        private PipelineDataUserFilter $filter,
    ) {}

    public function apply(SelectBuilder $queryBuilder): void
    {
        if ($this->user->isAdmin()) {
            return;
        }

        $data = $this->pipelineDataProvider->get();
        $data = $this->filter->filter($data);

        $entityTypes = array_keys($data);

        $ids = [];

        foreach ($data as $items) {
            foreach ($items as $item) {
                $ids[] = $item->id;
            }
        }

        $alias = 'pipelineAccess';

        $queryBuilder
            ->join(PipelineStage::FIELD_PIPELINE, $alias)
            ->where([
                $alias . '.' . Pipeline::FIELD_ENTITY_TYPE => $entityTypes,
                $alias . '.' . Attribute::ID => $ids,
            ]);
    }
}
