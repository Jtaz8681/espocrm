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

namespace Espo\Modules\Crm\Tools\Opportunity\Report;

use Espo\Core\Utils\Metadata;
use Espo\Modules\Crm\Entities\Opportunity as OpportunityEntity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\SelectBuilder;

class Util
{
    private Metadata $metadata;
    private EntityManager $entityManager;

    public function __construct(
        Metadata $metadata,
        EntityManager $entityManager
    ) {
        $this->metadata = $metadata;
        $this->entityManager = $entityManager;
    }

    /**
     * A grouping-by with distinct will give wrong results. Need to use sub-query.
     *
     * @param array<string|int, mixed> $whereClause
     */
    public function handleDistinctReportQueryBuilder(SelectBuilder $queryBuilder, array $whereClause): void
    {
        if (!$queryBuilder->build()->isDistinct()) {
            return;
        }

        $subQuery = $this->entityManager
            ->getQueryBuilder()
            ->select()
            ->from(OpportunityEntity::ENTITY_TYPE)
            ->select(Attribute::ID)
            ->where($whereClause)
            ->build();

        $queryBuilder->where([
            'id=s' => $subQuery,
        ]);
    }

    /**
     * @return string[]
     */
    public function getLostStageList(): array
    {
        $list = [];

        $probabilityMap =  $this->metadata
            ->get(['entityDefs', OpportunityEntity::ENTITY_TYPE, 'fields', 'stage', 'probabilityMap']) ?? [];

        $stageList = $this->metadata->get('entityDefs.Opportunity.fields.stage.options', []);

        foreach ($stageList as $stage) {
            $value = $probabilityMap[$stage] ?? 0;

            if (!$value) {
                $list[] = $stage;
            }
        }

        return $list;
    }

    /**
     * @return string[]
     */
    public function getWonStageList(): array
    {
        $list = [];

        $probabilityMap =  $this->metadata
            ->get(['entityDefs', OpportunityEntity::ENTITY_TYPE, 'fields', 'stage', 'probabilityMap']) ?? [];

        $stageList = $this->metadata->get('entityDefs.Opportunity.fields.stage.options', []);

        foreach ($stageList as $stage) {
            $value = $probabilityMap[$stage] ?? 0;

            if ($value == 100) {
                $list[] = $stage;
            }
        }

        return $list;
    }
}
