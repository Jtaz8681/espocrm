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

namespace Espo\Core\Formula\Functions\RecordGroup;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\NotAllowedUsage;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Core\Formula\Functions\RecordGroup\Util\FindQueryUtil;
use Espo\Core\Select\Primary\Filters\All;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;

/**
 * @noinspection PhpUnused
 */
class FindManyType implements Func
{
    public function __construct(
        private EntityManager $entityManager,
        private SelectBuilderFactory $selectBuilderFactory,
        private FindQueryUtil $findQueryUtil,
    ) {}

    /**
     * @return string[]
     * @inheritDoc
     */
    public function process(EvaluatedArgumentList $arguments): array
    {
        if (count($arguments) < 2) {
            throw TooFewArguments::create(2);
        }

        $entityType = $arguments[0];
        $limit = $arguments[1];
        $orderBy = $arguments[2] ?? null;
        $order = $arguments[3] ?? null;

        if (!is_string($entityType)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!is_int($limit)) {
            throw BadArgumentType::create(2, 'int');
        }

        if ($orderBy !== null && !is_string($orderBy)) {
            throw BadArgumentType::create(3, 'string|null');
        }

        if ($order !== null && !is_bool($order) && !is_string($order)) {
            throw BadArgumentType::create(4, 'string|bool|null');
        }

        $builder = $this->selectBuilderFactory
            ->create()
            ->withPrimaryFilter(All::NAME)
            ->from($entityType);

        $this->findQueryUtil->applyOrder($builder, $orderBy, $order, 4);

        $whereClause = [];

        if (count($arguments) <= 5) {
            $filter = null;

            if (count($arguments) === 5) {
                $filter = $arguments[4];
            }

            $this->findQueryUtil->applyFilter($builder, $filter, 5);
        } else {
            $i = 4;

            while ($i < count($arguments) - 1) {
                $key = $arguments[$i];
                $value = $arguments[$i + 1];

                $this->findQueryUtil->assertWhereClauseKeyValid($entityType, $key);

                $whereClause[] = [$key => $value];

                $i = $i + 2;
            }
        }

        try {
            $queryBuilder = $builder->buildQueryBuilder();
        } catch (BadRequest|Forbidden $e) {
            throw new NotAllowedUsage($e->getMessage(), $e->getCode(), $e);
        }

        if (!empty($whereClause)) {
            $queryBuilder->where($whereClause);
        }

        $queryBuilder
            ->select([Attribute::ID])
            ->limit(0, $limit);

        $collection = $this->entityManager
            ->getRDBRepository($entityType)
            ->clone($queryBuilder->build())
            ->find();

        return array_map(
            fn (Entity $entity) => $entity->getId(),
            iterator_to_array($collection)
        );
    }
}
