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

namespace Espo\Modules\Crm\Hooks\Opportunity;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Utils\Metadata;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\SaveOptions;

/**
 * @implements BeforeSave<Opportunity>
 */
class LastStage implements BeforeSave
{
    public static int $order = 8;

    public function __construct(private Metadata $metadata) {}

    /**
     * @param Opportunity $entity
     */
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if (
            $entity->isAttributeChanged('lastStage') ||
            !$entity->isAttributeChanged('stage')
        ) {
            return;
        }

        $stage = $entity->getStage();

        $probability = $this->metadata->get("entityDefs.Opportunity.fields.stage.probabilityMap.$stage");

        if ($probability !== 0) {
            $entity->setLastStage($entity->getStage());

            return;
        }

        // Lost.

        $probabilityMap = $this->getProbabilityMap();
        $stageList = $this->getStageList();

        if (!count($stageList)) {
            return;
        }

        if ($entity->isNew()) {
            // Created as Lost.

            $min = 100;
            $minStage = null;

            foreach ($stageList as $stage) {
                $itemProbability = $probabilityMap[$stage] ?? null;

                if (
                    $itemProbability === null ||
                    $itemProbability == 100 ||
                    $itemProbability === 0 ||
                    $itemProbability >= $min
                ) {
                    continue;
                }

                $min = $itemProbability;
                $minStage = $stage;
            }

            if (!$minStage) {
                return;
            }

            $entity->setLastStage($minStage);

            return;
        }

        // Won changed to Lost.

        if (!$entity->getLastStage()) {
            return;
        }

        $lastStageProbability = $probabilityMap[$entity->getLastStage()] ?? null;

        if ($lastStageProbability !== 100) {
            return;
        }

        $max = 0;
        $maxStage = null;

        foreach ($stageList as $stage) {
            $itemProbability = $probabilityMap[$stage] ?? null;

            if (
                $itemProbability === null ||
                $itemProbability === 100 ||
                $itemProbability === 0 ||
                $itemProbability <= $max
            ) {
                continue;
            }

            $max = $itemProbability;
            $maxStage = $stage;
        }

        if (!$maxStage) {
            return;
        }

        $entity->setLastStage($maxStage);
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
