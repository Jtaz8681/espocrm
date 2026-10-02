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

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\Entities\Pipeline;
use Espo\Entities\PipelineStage;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Order;
use Espo\ORM\Query\SelectBuilder;

class MoveService
{
    public const string TYPE_TOP = 'top';
    public const string TYPE_BOTTOM = 'bottom';
    public const string TYPE_UP = 'up';
    public const string TYPE_DOWN = 'down';

    public function __construct(
        private EntityManager $entityManager,
        private SelectBuilderFactory $selectBuilderFactory,
        private CacheClearer $cacheClearer,
    ) {}

    /**
     * @param self::TYPE_TOP|self::TYPE_BOTTOM|self::TYPE_UP|self::TYPE_DOWN $type
     * @throws BadRequest
     * @throws Forbidden
     */
    public function move(
        Pipeline $entity,
        string $type,
        SearchParams $searchParams,
    ): void {

        $builder = $this->createSelectBuilder($searchParams, $entity);

        if ($type === self::TYPE_TOP) {
            $this->moveToTop($entity, $builder);
        } else if ($type === self::TYPE_BOTTOM) {
            $this->moveToBottom($entity, $builder);
        } else if ($type === self::TYPE_UP) {
            $this->moveUp($entity, $builder);
        } else {
            $this->moveDown($entity, $builder);
        }

        $this->reOrder($entity::class);

        $this->cacheClearer->clear();
    }

    /**
     * @param class-string<Pipeline|PipelineStage> $className
     */
    public function reOrder(string $className, ?string $parentId = null): void
    {
        $this->entityManager
            ->getTransactionManager()
            ->run(fn () => $this->reOrderInternal($className, $parentId));
    }

    /**
     * @param class-string<Pipeline|PipelineStage> $className
     */
    private function reOrderInternal(string $className, ?string $parentId = null): void
    {
        $this->entityManager
            ->getRDBRepositoryByClass($className)
            ->select(Attribute::ID)
            ->forUpdate()
            ->sth()
            ->find();

        $builder = $this->entityManager
            ->getRDBRepositoryByClass($className)
            ->sth()
            ->order(Pipeline::FIELD_ORDER);

        if ($className === PipelineStage::class) {
            $builder->where([
                PipelineStage::ATTR_PIPELINE_ID => $parentId,
            ]);
        }

        $collection = $builder->find();

        foreach ($collection as $i => $entity) {
            $order = $i + 1;

            if ($entity->getOrder() === $order) {
                continue;
            }

            $entity->set(Pipeline::FIELD_ORDER, $order);

            $this->entityManager->saveEntity($entity, [SaveOption::SKIP_HOOKS => true]);
        }
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    private function createSelectBuilder(
        SearchParams $searchParams,
        Pipeline $entity,
    ): SelectBuilder {

        /** @noinspection PhpRedundantOptionalArgumentInspection */
        return $this->selectBuilderFactory
            ->create()
            ->from($entity::ENTITY_TYPE)
            ->withSearchParams($searchParams)
            ->withStrictAccessControl()
            ->buildQueryBuilder()
            ->limit(null, null)
            ->order([]);
    }

    private function moveUp(
        Pipeline $entity,
        SelectBuilder $builder,
    ): void {

        $query = $builder
            ->where([Pipeline::FIELD_ORDER . '<' => $entity->getOrder()])
            ->order(Pipeline::FIELD_ORDER, Order::DESC)
            ->build();

        $another = $this->entityManager
            ->getRDBRepositoryByClass($entity::class)
            ->clone($query)
            ->findOne();

        if (!$another) {
            return;
        }

        $index = $entity->getOrder();

        $entity->set(Pipeline::FIELD_ORDER, $another->getOrder());
        $another->set(Pipeline::FIELD_ORDER, $index);

        $this->entityManager->saveEntity($entity);
        $this->entityManager->saveEntity($another);
    }

    private function moveDown(
        Pipeline $entity,
        SelectBuilder $builder,
    ): void {

        $query = $builder
            ->where([Pipeline::FIELD_ORDER . '>' => $entity->getOrder()])
            ->order(Pipeline::FIELD_ORDER, Order::ASC)
            ->build();

        $another = $this->entityManager
            ->getRDBRepositoryByClass($entity::class)
            ->clone($query)
            ->findOne();

        if (!$another) {
            return;
        }

        $index = $entity->getOrder();

        $entity->set(Pipeline::FIELD_ORDER, $another->getOrder());
        $another->set(Pipeline::FIELD_ORDER, $index);

        $this->entityManager->saveEntity($entity);
        $this->entityManager->saveEntity($another);

        $this->entityManager->refreshEntity($entity);
        $this->entityManager->refreshEntity($another);
    }

    private function moveToTop(
        Pipeline $entity,
        SelectBuilder $builder,
    ): void {

        $query = $builder
            ->where([Pipeline::FIELD_ORDER . '<' => $entity->getOrder()])
            ->order(Pipeline::FIELD_ORDER, Order::ASC)
            ->build();

        $another = $this->entityManager
            ->getRDBRepositoryByClass($entity::class)
            ->clone($query)
            ->findOne();

        if (!$another) {
            return;
        }

        $entity->set(Pipeline::FIELD_ORDER, $another->getOrder() - 1);

        $this->entityManager->saveEntity($entity);
    }

    private function moveToBottom(
        Pipeline $entity,
        SelectBuilder $builder,
    ): void {

        $query = $builder
            ->where([Pipeline::FIELD_ORDER . '>' => $entity->getOrder()])
            ->order(Pipeline::FIELD_ORDER, Order::DESC)
            ->build();

        $another = $this->entityManager
            ->getRDBRepositoryByClass($entity::class)
            ->clone($query)
            ->findOne();

        if (!$another) {
            return;
        }

        $entity->set(Pipeline::FIELD_ORDER, $another->getOrder() + 1);

        $this->entityManager->saveEntity($entity);
    }
}
