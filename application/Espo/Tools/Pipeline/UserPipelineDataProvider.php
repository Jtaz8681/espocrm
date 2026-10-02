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

namespace Espo\Tools\Pipeline;

use Espo\Tools\Pipeline\Data\PipelineData;

/**
 * @since 10.0.0
 */
class UserPipelineDataProvider
{
    public function __construct(
        private PipelineDataProvider $pipelineDataProvider,
        private PipelineDataUserFilter $userFilter,
    ) {}

    /**
     * @return PipelineData[]
     */
    public function getForEntityType(string $entityType): array
    {
        return $this->get()[$entityType] ?? [];
    }

    /**
     * @return array<string, PipelineData[]>
     */
    private function get(): array
    {
        $pipelines = $this->pipelineDataProvider->get();

        return $this->userFilter->filter($pipelines);
    }
}
