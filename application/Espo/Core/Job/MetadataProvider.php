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

namespace Espo\Core\Job;

use Espo\Core\Utils\Metadata;

class MetadataProvider
{
    public function __construct(private Metadata $metadata)
    {}

    /**
     * @return string[]
     */
    public function getPreparableJobNameList(): array
    {
        $list = [];

        $items = $this->metadata->get(['app', 'scheduledJobs']) ?? [];

        foreach ($items as $name => $item) {
            $isPreparable = (bool) ($item['preparatorClassName'] ?? null);

            if ($isPreparable) {
                $list[] = $name;
            }
        }

        return $list;
    }

    public function isJobSystem(string $name): bool
    {
        return (bool) $this->metadata->get(['app', 'scheduledJobs', $name, 'isSystem']);
    }

    public function isJobPreparable(string $name): bool
    {
        return (bool) $this->metadata->get(['app', 'scheduledJobs', $name, 'preparatorClassName']);
    }

    public function getPreparatorClassName(string $name): ?string
    {
        return $this->metadata->get(['app', 'scheduledJobs', $name, 'preparatorClassName']);
    }

    public function getJobClassName(string $name): ?string
    {
        return $this->metadata->get(['app', 'scheduledJobs', $name, 'jobClassName']);
    }

    /**
     * @return string[]
     */
    public function getScheduledJobNameList(): array
    {
        /** @var array<string, mixed> $items */
        $items = $this->metadata->get(['app', 'scheduledJobs']) ?? [];

        return array_keys($items);
    }

    /**
     * @return string[]
     */
    public function getNonSystemScheduledJobNameList(): array
    {
        return array_filter(
            $this->getScheduledJobNameList(),
            function (string $item) {
                $isSystem = (bool) $this->metadata->get(['app', 'scheduledJobs', $item, 'isSystem']);

                return !$isSystem;
            }
        );
    }
}
