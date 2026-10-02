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

namespace Espo\Core\Repositories;

use Espo\ORM\Entity;
use Espo\ORM\Mapper\BaseMapper;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Order;

/**
 * @template TEntity of Entity
 * @extends Database<Entity>
 */
class CategoryTree extends Database
{
    private const string ATTR_ORDER = 'order';
    private const string ATTR_PARENT_ID = 'parentId';

    private const string PATH_ATTR_ASCENDOR_ID = 'ascendorId';
    private const string PATH_ATTR_DESCENDOR_ID = 'descendorId';

    /**
     * @inheritDoc
     */
    public function save(Entity $entity, array $options = []): void
    {
        $this->entityManager
            ->getTransactionManager()
            ->run(function () use ($entity, $options) {
                if (!$entity->isNew() && $entity->isAttributeChanged(self::ATTR_PARENT_ID)) {
                    $this->lockPathEntries($entity);
                }

                parent::save($entity, $options);
            });
    }

    /**
     * @inheritDoc
     */
    public function remove(Entity $entity, array $options = []): void
    {
        $this->entityManager
            ->getTransactionManager()
            ->run(function () use ($entity, $options) {
                $this->lockPathEntries($entity);

                parent::remove($entity, $options);
            });
    }

    private function lockPathEntries(Entity $entity): void
    {
        $this->entityManager
            ->getRDBRepository($this->getPathEntityType())
            ->sth()
            ->select(Attribute::ID)
            ->where([
                'OR' => [
                    self::PATH_ATTR_ASCENDOR_ID => $entity->getId(),
                    self::PATH_ATTR_DESCENDOR_ID => $entity->getId(),
                ]
            ])
            ->forUpdate()
            ->find();
    }

    protected function beforeSave(Entity $entity, array $options = [])
    {
        if ($entity->get(self::ATTR_ORDER) === null && $entity->hasAttribute(self::ATTR_ORDER)) {
            $this->setOrderToEnd($entity);
        }

        parent::beforeSave($entity, $options);
    }

    /**
     * @param array<string, mixed> $options
     * @return void
     */
    protected function afterSave(Entity $entity, array $options = [])
    {
        parent::afterSave($entity, $options);

        $parentId = $entity->get(self::ATTR_PARENT_ID);

        $em = $this->entityManager;

        $pathEntityType = $this->getPathEntityType();

        if ($entity->isNew()) {
            if ($parentId) {
                $subSelect1 = $em->getQueryBuilder()
                    ->select()
                    ->from($pathEntityType)
                    ->select(['ascendorId', "'" . $entity->getId() . "'"])
                    ->where([
                        'descendorId' => $parentId,
                    ])
                    ->build();

                $subSelect2 = $em->getQueryBuilder()
                    ->select()
                    ->select(["'" . $entity->getId() . "'", "'" . $entity->getId() . "'"])
                    ->build();

                $select = $em->getQueryBuilder()
                    ->union()
                    ->all()
                    ->query($subSelect1)
                    ->query($subSelect2)
                    ->build();

                $insert = $em->getQueryBuilder()
                    ->insert()
                    ->into($pathEntityType)
                    ->columns(['ascendorId', 'descendorId'])
                    ->valuesQuery($select)
                    ->build();

                $em->getQueryExecutor()->execute($insert);

                return;
            }

            $insert = $em->getQueryBuilder()
                ->insert()
                ->into($pathEntityType)
                ->columns(['ascendorId', 'descendorId'])
                ->values([
                    'ascendorId' => $entity->getId(),
                    'descendorId' => $entity->getId(),
                ])
                ->build();

            $em->getQueryExecutor()->execute($insert);

            return;
        }

        if (!$entity->isAttributeChanged(self::ATTR_PARENT_ID)) {
            return;
        }

        $delete = $em->getQueryBuilder()
            ->delete()
            ->from($pathEntityType, 'a')
            ->join(
                $pathEntityType,
                'd',
                [
                    'd.descendorId:' => 'a.descendorId',
                ]
            )
            ->leftJoin(
                $pathEntityType,
                'x',
                [
                    'x.ascendorId:' => 'd.descendorId',
                    'x.descendorId:' => 'a.ascendorId',
                ]
            )
            ->where([
                'd.descendorId' => $entity->getId(),
                'x.ascendorId' => null,
            ])
            ->build();

        $em->getQueryExecutor()->execute($delete);

        if (!empty($parentId)) {
            $select = $em->getQueryBuilder()
                ->select()
                ->from($pathEntityType)
                ->select(['ascendorId', 's.descendorId'])
                ->join($pathEntityType, 's')
                ->where([
                    's.ascendorId' => $entity->getId(),
                    'descendorId' => $parentId,
                ])
                ->build();

            $insert = $em->getQueryBuilder()
                ->insert()
                ->into($pathEntityType)
                ->columns(['ascendorId', 'descendorId'])
                ->valuesQuery($select)
                ->build();

            $em->getQueryExecutor()->execute($insert);
        }
    }

    protected function afterRemove(Entity $entity, array $options = [])
    {
        parent::afterRemove($entity, $options);

        $pathEntityType = $this->getPathEntityType();

        $em = $this->entityManager;

        $delete = $em->getQueryBuilder()
            ->delete()
            ->from($pathEntityType)
            ->where([
                'descendorId' => $entity->getId(),
            ])
            ->build();

        $em->getQueryExecutor()->execute($delete);

        $mapper = $em->getMapper();

        if (!$mapper instanceof BaseMapper) {
            return;
        }

        $mapper->deleteFromDb($entity->getEntityType(), $entity->getId());
    }

    private function setOrderToEnd(Entity $entity): void
    {
        $parentId = $entity->get(self::ATTR_PARENT_ID);

        $where = [self::ATTR_PARENT_ID => $parentId];

        if (!$entity->isNew()) {
            $where[Attribute::ID . '!='] = $entity->getId();
        }

        $last = $this
            ->where($where)
            ->order(self::ATTR_ORDER, Order::DESC)
            ->findOne();

        $order = $last ? ($last->get(self::ATTR_ORDER) + 1) : 1;

        $entity->set(self::ATTR_ORDER, $order);
    }

    private function getPathEntityType(): string
    {
        return $this->entityType . 'Path';
    }
}
