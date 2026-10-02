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

namespace Espo\Modules\Crm\Classes\Select\Opportunity\Utils;

use Espo\Core\Utils\Metadata;

class StageListProvider
{
    public function __construct(private Metadata $metadata)
    {}

    /**
     * @return string[]
     */
    public function getLost(): array
    {
        $output = [];

        $probabilityMap = $this->getProbabilityMap();

        foreach ($this->getStageList() as $stage) {
            $value = $probabilityMap[$stage] ?? null;

            if ($value === 0 || $value === 0.0) {
                $output[] = $stage;
            }
        }

        return $output;
    }

    /**
     * @return string[]
     */
    public function getWon(): array
    {
        $output = [];

        $probabilityMap = $this->getProbabilityMap();

        foreach ($this->getStageList() as $stage) {
            $value = $probabilityMap[$stage] ?? null;

            if ($value == 100) {
                $output[] = $stage;
            }
        }

        return $output;
    }

    /**
     * @return array<string, ?int>
     */
    private function getProbabilityMap(): array
    {
        /** @var array<string, ?int> $probabilityMap */
        $probabilityMap = $this->metadata->get('entityDefs.Opportunity.fields.stage.probabilityMap') ?? [];

        return $probabilityMap;
    }

    /**
     * @return string[]
     */
    private function getStageList(): array
    {
        return $this->metadata->get('entityDefs.Opportunity.fields.stage.options') ?? [];
    }
}
