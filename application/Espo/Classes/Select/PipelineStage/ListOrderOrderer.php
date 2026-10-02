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

namespace Espo\Classes\Select\PipelineStage;

use Espo\Core\Select\Order\Item;
use Espo\Core\Select\Order\Orderer;
use Espo\Entities\PipelineStage;
use Espo\ORM\Query\SelectBuilder;

/**
 * @noinspection PhpUnused
 */
class ListOrderOrderer implements Orderer
{
    public function apply(SelectBuilder $queryBuilder, Item $item): void
    {
        $queryBuilder
            ->leftJoin(PipelineStage::FIELD_PIPELINE)
            ->order(PipelineStage::FIELD_PIPELINE . '.' . PipelineStage::FIELD_ORDER, $item->getOrder())
            ->order(PipelineStage::FIELD_ORDER, $item->getOrder());
    }
}
