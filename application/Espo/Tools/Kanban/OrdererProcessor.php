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
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Core\Utils\Id\RecordIdGenerator;
use Espo\Entities\KanbanOrder;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Name\Attribute;
use LogicException;

class OrdererProcessor
{
    private const MAX_GROUP_LENGTH = 100;
    private const DEFAULT_MAX_NUMBER = 50;

    private ?string $entityType = null;
    private ?string $group = null;
    private ?string $userId = null;
    private bool $isPipeline = false;

    private int $maxNumber = self::DEFAULT_MAX_NUMBER;

    public function __construct(
        private EntityManager $entityManager,
        private Metadata $metadata,
        private RecordIdGenerator $idGenerator,
        private MetadataProvider $metadataProvider,
    ) {}

    /**
     * @since 10.0.0
     */
    public function setIsPipeline(bool $isPipeline): self
    {
        $this->isPipeline = $isPipeline;

        return $this;
    }

    public function setEntityType(string $entityType): self
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function setGroup(string $group): self
    {
        $this->group = mb_substr($group, 0, self::MAX_GROUP_LENGTH);

        return $this;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function setMaxNumber(?int $maxNumber): self
    {
        if ($maxNumber === null) {
            $this->maxNumber = self::DEFAULT_MAX_NUMBER;

            return $this;
        }

        $this->maxNumber = $maxNumber;

        return $this;
    }

    /**
     * @param string[] $ids
     *
     * @throws ForbiddenSilent
     * @throws Error
     * @throws BadRequest
     */
    public function order(array $ids): void
    {
        $this->validate();

        $count = count($ids);

        if (!$count) {
            return;
        }

        $this->entityManager
            ->getTransactionManager()
            ->start();

        $deleteQuery1 = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from(KanbanOrder::ENTITY_TYPE)
            ->where([
                'entityType' => $this->entityType,
                'userId' => $this->userId,
                'entityId' => $ids,
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($deleteQuery1);

        $minOrder = null;

        $first = $this->entityManager
            ->getRDBRepository(KanbanOrder::ENTITY_TYPE)
            ->select([Attribute::ID, 'order'])
            ->where([
                'entityType' => $this->entityType,
                'userId' => $this->userId,
                'group' => $this->group,
            ])
            ->order('order')
            ->findOne();

        if ($first) {
            $minOrder = $first->get('order');
        }

        if ($minOrder !== null) {
            $offset = $count - $minOrder;

            $updateQuery = $this->entityManager
                ->getQueryBuilder()
                ->update()
                ->in(KanbanOrder::ENTITY_TYPE)
                ->where([
                    'entityType' => $this->entityType,
                    'group' => $this->group,
                    'userId' => $this->userId,
                ])
                ->set([
                    'order:' => 'ADD:(order, ' . strval($offset) . ')'
                ])
                ->build();

            $this->entityManager->getQueryExecutor()->execute($updateQuery);
        }

        $collection = $this->entityManager
            ->getCollectionFactory()
            ->create(KanbanOrder::ENTITY_TYPE);

        foreach ($ids as $i => $id) {
            $item = $this->entityManager->getNewEntity(KanbanOrder::ENTITY_TYPE);

            $item->set([
                'id' => $this->idGenerator->generate(),
                'entityId' => $id,
                'entityType' => $this->entityType,
                'group' => $this->group,
                'userId' => $this->userId,
                'order' => $i,
            ]);

            $collection[] = $item;
        }

        $this->entityManager->getMapper()->massInsert($collection);

        $deleteQuery2 = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from(KanbanOrder::ENTITY_TYPE)
            ->where([
                'entityType' => $this->entityType,
                'group' => $this->group,
                'userId' => $this->userId,
                'order>' => $this->maxNumber,
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($deleteQuery2);

        $this->entityManager
            ->getTransactionManager()
            ->commit();
    }

    /**
     * @throws Error
     * @throws ForbiddenSilent
     * @throws BadRequest
     */
    private function validate(): void
    {
        if (!$this->entityType) {
            throw new LogicException("No entity type.");
        }

        if (!$this->group) {
            throw new LogicException("No group.");
        }

        if (!$this->userId) {
            throw new LogicException("No user ID.");
        }

        if (!$this->metadata->get(['scopes', $this->entityType, 'object'])) {
            throw new LogicException("Not allowed entity type.");
        }

        $orderDisabled = $this->metadata->get(['scopes', $this->entityType, 'kanbanOrderDisabled']);

        if ($orderDisabled) {
            throw new LogicException("Order is disabled.");
        }

        if ($this->isPipeline) {
            return;
        }

        $statusField = $this->metadataProvider->getStatusField($this->entityType);

        if (!$statusField) {
            throw new LogicException("Not status field.");
        }

        $statusList = $this->metadataProvider->getStatusList($this->entityType);

        if (!in_array($this->group, $statusList)) {
            throw new ForbiddenSilent("Group is not available in status list.");
        }
    }
}
