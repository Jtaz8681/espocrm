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
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\FieldProcessing\ListLoadProcessor;
use Espo\Core\FieldProcessing\Loader\Params as FieldLoaderParams;
use Espo\Core\Record\Collection;
use Espo\Core\Record\Select\ApplierClassNameListProvider;
use Espo\Core\Record\ServiceContainer as RecordServiceContainer;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Tools\Pipeline\Data\PipelineData;
use Espo\Tools\Pipeline\Data\StageData;
use Espo\Tools\Pipeline\PipelineDataProvider;

class Kanban
{
    private const DEFAULT_MAX_ORDER_NUMBER = 50;
    private const MAX_GROUP_LENGTH = 100;

    private ?string $entityType = null;
    private bool $countDisabled = false;
    private bool $orderDisabled = false;
    private ?SearchParams $searchParams = null;
    private ?string $userId = null;
    private int $maxOrderNumber = self::DEFAULT_MAX_ORDER_NUMBER;
    private ?string $pipelineId = null;

    public function __construct(
        private SelectBuilderFactory $selectBuilderFactory,
        private EntityManager $entityManager,
        private ListLoadProcessor $listLoadProcessor,
        private RecordServiceContainer $recordServiceContainer,
        private ApplierClassNameListProvider $applierClassNameListProvider,
        private MetadataProvider $metadataProvider,
        private PipelineDataProvider $pipelineDataProvider,
    ) {}

    public function setEntityType(string $entityType): self
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function setSearchParams(SearchParams $searchParams): self
    {
        $this->searchParams = $searchParams;

        return $this;
    }

    /**
     * @since 10.0.0
     */
    public function setPipelineId(?string $pipelineId): self
    {
        $this->pipelineId = $pipelineId;

        return $this;
    }

    public function setCountDisabled(bool $countDisabled): self
    {
        $this->countDisabled = $countDisabled;

        return $this;
    }

    public function setOrderDisabled(bool $orderDisabled): self
    {
        $this->orderDisabled = $orderDisabled;

        return $this;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function setMaxOrderNumber(?int $maxOrderNumber): self
    {
        if ($maxOrderNumber === null) {
            $this->maxOrderNumber = self::DEFAULT_MAX_ORDER_NUMBER;

            return $this;
        }

        $this->maxOrderNumber = $maxOrderNumber;

        return $this;
    }

    /**
     * Get kanban record data.
     *
     * @throws Error
     * @throws Forbidden
     * @throws BadRequest
     */
    public function getResult(): Result
    {
        if (!$this->entityType) {
            throw new Error("Entity type is not specified.");
        }

        if (!$this->searchParams) {
            throw new Error("No search params.");
        }

        $searchParams = $this->searchParams;

        $recordService = $this->recordServiceContainer->get($this->entityType);

        $maxSize = $searchParams->getMaxSize();

        if ($this->countDisabled && $maxSize) {
            $searchParams = $searchParams->withMaxSize($maxSize + 1);
        }

        $query = $this->selectBuilderFactory
            ->create()
            ->from($this->entityType)
            ->withStrictAccessControl()
            ->withSearchParams($searchParams)
            ->withAdditionalApplierClassNameList(
                $this->applierClassNameListProvider->get($this->entityType)
            )
            ->build();

        $statusField = $this->metadataProvider->getStatusField($this->entityType);
        $statusList = $this->metadataProvider->getStatusList($this->entityType, $this->pipelineId);
        $groupList = [];

        $repository = $this->entityManager->getRDBRepository($this->entityType);

        $hasMore = false;

        $pipeline = $this->getPipelineData($this->entityType);

        foreach ($statusList as $status) {
            $itemSelectBuilder = $this->entityManager
                ->getQueryBuilder()
                ->select()
                ->clone($query);

            $itemSelectBuilder->where([
                $statusField => $status,
            ]);

            $itemQuery = $itemSelectBuilder->build();

            $newOrder = $itemQuery->getOrder();

            array_unshift($newOrder, [
                'COALESCE:(kanbanOrder.order, ' . ($this->maxOrderNumber + 1) . ')',
                'ASC',
            ]);

            if ($this->userId && !$this->orderDisabled) {
                $group = mb_substr($status, 0, self::MAX_GROUP_LENGTH);

                $itemQuery = $this->entityManager
                    ->getQueryBuilder()
                    ->select()
                    ->clone($itemQuery)
                    ->order($newOrder)
                    ->leftJoin(
                        'KanbanOrder',
                        'kanbanOrder',
                        [
                            'kanbanOrder.entityType' => $this->entityType,
                            'kanbanOrder.entityId:' => 'id',
                            'kanbanOrder.group' => $group,
                            'kanbanOrder.userId' => $this->userId,
                        ]
                    )
                    ->build();
            }

            $collectionSub = $repository
                ->clone($itemQuery)
                ->find();

            if (!$this->countDisabled) {
                $totalSub = $repository->clone($itemQuery)->count();
            } else {
                $recordCollection = Collection::createNoCount($collectionSub, $maxSize);

                $collectionSub = $recordCollection->getCollection();
                $totalSub = $recordCollection->getTotal();

                if ($totalSub === Collection::TOTAL_HAS_MORE) {
                    $hasMore = true;
                }
            }

            $loadProcessorParams = FieldLoaderParams
                ::create()
                ->withSelect($searchParams->getSelect());

            foreach ($collectionSub as $e) {
                $this->listLoadProcessor->process($e, $loadProcessorParams);

                $recordService->prepareEntityForOutput($e);
            }

            /** @var Collection<Entity> $itemRecordCollection */
            $itemRecordCollection = new Collection($collectionSub, $totalSub);

            $stage = $this->getPipelineStageData($pipeline, $status);

            $groupList[] = new GroupItem(
                name: $status,
                collection: $itemRecordCollection,
                label: $stage?->name,
                style: $stage?->style,
            );
        }

        $total = !$this->countDisabled ?
            $repository->clone($query)->count() :
            ($hasMore ? Collection::TOTAL_HAS_MORE : Collection::TOTAL_HAS_NO_MORE);

        return new Result($groupList, $total);
    }

    private function getPipelineData(string $entityType): ?PipelineData
    {
        if (!$this->pipelineId) {
            return null;
        }

        $pipeline = null;

        $pipelines = $this->pipelineDataProvider->get()[$entityType] ?? [];

        foreach ($pipelines as $it) {
            if ($it->id === $this->pipelineId) {
                $pipeline = $it;

                break;
            }
        }

        return $pipeline;
    }

    private function getPipelineStageData(?PipelineData $pipeline, string $id): ?StageData
    {
        if ($pipeline) {
            foreach ($pipeline->stages as $it) {
                if ($it->id === $id) {
                    return $it;
                }
            }
        }

        return null;
    }
}
