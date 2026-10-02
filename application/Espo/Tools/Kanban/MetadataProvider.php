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

namespace Espo\Tools\Kanban;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Name\Field;
use Espo\Core\Utils\Metadata;
use Espo\Tools\Pipeline\Data\PipelineData;
use Espo\Tools\Pipeline\MetadataProvider as PipelineMetadataProvider;
use Espo\Tools\Pipeline\PipelineDataProvider;

class MetadataProvider
{
    public function __construct(
        private Metadata $metadata,
        private PipelineMetadataProvider $pipelineMetadata,
        private PipelineDataProvider $pipelineDataProvider,
    ) {}

    /**
     * @return string[]
     * @throws Error
     * @throws BadRequest
     */
    public function getStatusList(string $entityType, ?string $pipelineId = null): array
    {
        if ($this->pipelineMetadata->isEnabled($entityType)) {
            if (!$pipelineId) {
                throw BadRequest::createWithBody(
                    'noPipeline',
                    Error\Body::create()->withMessageTranslation('noPipeline')
                );
            }

            return $this->getPipelineStatusList($entityType, $pipelineId);
        }

        $field = $this->getStatusField($entityType);

        $statusList = $this->metadata->get("entityDefs.$entityType.fields.$field.options");
        $optionsReference = $this->metadata->get("entityDefs.$entityType.fields.$field.optionsReference");

        if (is_string($optionsReference) && str_contains($optionsReference, '.')) {
            [$refEntityType, $refField] = explode('.', $optionsReference);

            $statusList = $this->metadata->get("entityDefs.$refEntityType.fields.$refField.options");
        }

        if (!$statusList) {
            throw new Error("No options for status field for entity type '$entityType'.");
        }

        $statusList = array_diff($statusList, $this->getStatusIgnoreList($entityType));
        $statusList = array_filter($statusList, fn ($it) => $it !== '');

        return array_values($statusList);
    }

    /**
     * @throws Error
     */
    public function getStatusField(string $entityType): string
    {
        if ($this->pipelineMetadata->isEnabled($entityType)) {
            return Field::PIPELINE_STAGE . 'Id';
        }

        $statusField = $this->metadata->get("scopes.$entityType.statusField");

        if (!$statusField) {
            throw new Error("No status field for entity type '$entityType'.");
        }

        return $statusField;
    }

    /**
     * @return string[]
     */
    public function getStatusIgnoreList(string $entityType): array
    {
        return $this->metadata->get("scopes.$entityType.kanbanStatusIgnoreList") ?? [];
    }

    private function getPipelineData(string $entityType, string $pipelineId): ?PipelineData
    {
        $pipeline = null;

        $pipelines = $this->pipelineDataProvider->get()[$entityType] ?? [];

        foreach ($pipelines as $it) {
            if ($it->id === $pipelineId) {
                $pipeline = $it;

                break;
            }
        }

        return $pipeline;
    }

    /**
     * @return string[]
     */
    private function getPipelineStatusList(string $entityType, string $pipelineId): array
    {
        $pipeline = $this->getPipelineData($entityType, $pipelineId);

        if (!$pipeline) {
            return [];
        }

        $ignoreStatusList = $this->getStatusIgnoreList($entityType);
        $output = [];

        foreach ($pipeline->stages as $stage) {
            if (in_array($stage->mappedStatus, $ignoreStatusList)) {
                continue;
            }

            $output[] = $stage->id;
        }

        return $output;
    }
}
