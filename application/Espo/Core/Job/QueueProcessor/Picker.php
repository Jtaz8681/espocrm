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

namespace Espo\Core\Job\QueueProcessor;

use Espo\Core\Job\QueueUtil;
use Espo\Entities\Job;
use RuntimeException;

/**
 * Picks jobs for a portion distributing by weights if needed.
 */
class Picker
{
    public function __construct(
        private QueueUtil $queueUtil,
    ) {}

    /**
     * @param Params $params
     * @return iterable<Job>
     */
    public function pick(Params $params): iterable
    {
        $paramsList = $params->getSubQueueParamsList();

        if (!$paramsList) {
            return $this->queueUtil->getPendingJobs($params);
        }

        $groups = [];

        foreach ($paramsList as $itemParams) {
            $groups[] = iterator_to_array($this->queueUtil->getPendingJobs($itemParams));
        }

        return $this->pickJobsRecursively($paramsList, $groups, $params->getLimit());
    }

    /**
     * @param Params[] $paramsList,
     * @param Job[][] $groups
     * @return Job[]
     */
    private function pickJobsRecursively(
        array $paramsList,
        array $groups,
        int $limit,
    ): array {

        $totalWeight = array_reduce($paramsList, fn ($c, $it) => $c + $it->getWeight(), 0.0);

        /** @var Job[][] $leftovers */
        $leftovers = [];
        $output = [];

        foreach ($paramsList as $i => $itemParams) {
            if (!array_key_exists($i, $groups)) {
                throw new RuntimeException();
            }

            $jobs = $groups[$i];
            $weight = $itemParams->getWeight();

            $portion = (int) round($weight / $totalWeight * $limit);

            $pickedJobs = [];

            while (count($pickedJobs) < $portion) {
                if (count($jobs) === 0) {
                    break;
                }

                $pickedJobs[] = array_shift($jobs);
            }

            $output = array_merge($output, $pickedJobs);

            $leftovers[] = $jobs;
        }

        $left = $limit - count($output);
        $leftoverCount = array_reduce($leftovers, fn ($c, $it) => $c + count($it), 0);

        if ($left && $leftoverCount) {
            foreach ($leftovers as $i => $jobs) {
                if (count($jobs) === 0) {
                    unset($leftovers[$i]);
                    unset($paramsList[$i]);
                }
            }

            $leftovers = array_values($leftovers);
            $paramsList = array_values($paramsList);

            $rest = $this->pickJobsRecursively(
                paramsList: $paramsList,
                groups: $leftovers,
                limit: $leftoverCount,
            );

            $output = array_merge($output, $rest);
            $output = array_slice($output, 0, $limit);
        }

        return $output;
    }
}
