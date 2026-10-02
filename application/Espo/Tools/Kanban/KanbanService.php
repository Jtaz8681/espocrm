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

use Espo\Core\Acl\Table;
use Espo\Core\AclManager;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\InjectableFactory;
use Espo\Core\Name\Field;
use Espo\Core\Record\EntityProvider;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\Where\Item\Type;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Pipeline;
use Espo\Entities\PipelineStage;
use Espo\Entities\User;
use Espo\Tools\Pipeline\MetadataProvider as PipelineMetadataProvider;

class KanbanService
{
    public function __construct(
        private User $user,
        private AclManager $aclManager,
        private InjectableFactory $injectableFactory,
        private Config $config,
        private Metadata $metadata,
        private Orderer $orderer,
        private EntityProvider $entityProvider,
        private PipelineMetadataProvider $pipelineMetadata,
    ) {}

    /**
     * @throws Error
     * @throws Forbidden
     * @throws BadRequest
     * @throws NotFound
     */
    public function getData(string $entityType, SearchParams $searchParams): Result
    {
        $this->processAccessCheck($entityType);

        $disableCount = $this->metadata
            ->get(['entityDefs', $entityType, 'collection', 'countDisabled']) ?? false;

        $orderDisabled = $this->metadata
            ->get(['scopes', $entityType, 'kanbanOrderDisabled']) ?? false;

        $maxOrderNumber = $this->config->get('kanbanMaxOrderNumber');

        $pipeline = $this->getPipeline($entityType, $searchParams);

        return $this->createKanban()
            ->setEntityType($entityType)
            ->setSearchParams($searchParams)
            ->setPipelineId($pipeline?->getId() ?? null)
            ->setCountDisabled($disableCount)
            ->setOrderDisabled($orderDisabled)
            ->setUserId($this->user->getId())
            ->setMaxOrderNumber($maxOrderNumber)
            ->getResult();
    }

    /**
     * @param string[] $ids
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     * @throws BadRequest
     */
    public function order(string $entityType, string $group, array $ids): void
    {
        $this->processAccessCheck($entityType);

        if ($this->user->isPortal()) {
            throw new ForbiddenSilent("Kanban order is not allowed for portal users.");
        }

        $isPipeline = $this->pipelineMetadata->isEnabled($entityType);


        if ($isPipeline) {
            $this->entityProvider->getByClass(PipelineStage::class, $group);
        }

        $maxOrderNumber = $this->config->get('kanbanMaxOrderNumber');

        $this->orderer
            ->setEntityType($entityType)
            ->setGroup($group)
            ->setIsPipeline($isPipeline)
            ->setUserId($this->user->getId())
            ->setMaxNumber($maxOrderNumber)
            ->order($ids);
    }

    private function createKanban(): Kanban
    {
        return $this->injectableFactory->create(Kanban::class);
    }

    /**
     * @throws ForbiddenSilent
     */
    private function processAccessCheck(string $entityType): void
    {
        if (!$this->metadata->get(['scopes', $entityType, 'object'])) {
            throw new ForbiddenSilent("Non-object entities are not supported.");
        }

        if ($this->metadata->get(['recordDefs', $entityType, 'kanbanDisabled'])) {
            throw new ForbiddenSilent("Kanban is disabled for '$entityType'.");
        }

        if (!$this->aclManager->check($this->user, $entityType, Table::ACTION_READ)) {
            throw new ForbiddenSilent();
        }
    }


    private function getPipelineId(SearchParams $searchParams): ?string
    {
        $pipelineId = null;

        if ($searchParams->getWhere()?->getType() === Type::AND) {
            foreach ($searchParams->getWhere()->getItemList() as $item) {
                if (
                    $item->getType() === Type::EQUALS &&
                    $item->getAttribute() === Field::PIPELINE . 'Id'
                ) {
                    $pipelineId = $item->getValue();

                    break;
                }
            }
        }

        if ($pipelineId !== null && !is_string($pipelineId)) {
            return null;
        }

        return $pipelineId;
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    private function getPipeline(string $entityType, SearchParams $searchParams): ?Pipeline
    {
        if (!$this->pipelineMetadata->isEnabled($entityType)) {
            return null;
        }

        $pipeline = null;
        $pipelineId = $this->getPipelineId($searchParams);

        if ($pipelineId) {
            $pipeline = $this->entityProvider->getByClass(Pipeline::class, $pipelineId);
        }

        return $pipeline;
    }
}
