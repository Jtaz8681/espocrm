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
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;

/**
 * @noinspection PhpUnused
 */
class ExistsType implements Func
{
    public function __construct(
        private EntityManager $entityManager,
        private SelectBuilderFactory $selectBuilderFactory,
        private FindQueryUtil $findQueryUtil,
    ) {}

    public function process(EvaluatedArgumentList $arguments): bool
    {
        if (count($arguments) < 1) {
            throw TooFewArguments::create(1);
        }

        $entityType = $arguments[0];

        if (!is_string($entityType)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (count($arguments) <= 2) {
            $filter = null;

            if (count($arguments) === 2) {
                $filter = $arguments[1];
            }

            $builder = $this->selectBuilderFactory
                ->create()
                ->withPrimaryFilter(All::NAME)
                ->from($entityType);

            $this->findQueryUtil->applyFilter($builder, $filter, 2);

            try {
                return (bool) $this->entityManager
                    ->getRDBRepository($entityType)
                    ->clone($builder->build())
                    ->findOne();
            } catch (BadRequest|Forbidden $e) {
                throw new NotAllowedUsage($e->getMessage(), 0, $e);
            }
        }

        $whereClause = [];

        $i = 1;

        while ($i < count($arguments) - 1) {
            $key = $arguments[$i];
            $value = $arguments[$i + 1];

            $this->findQueryUtil->assertWhereClauseKeyValid($entityType, $key);

            $whereClause[] = [$key => $value];

            $i = $i + 2;
        }

        return (bool) $this->entityManager
            ->getRDBRepository($entityType)
            ->select([Attribute::ID])
            ->where($whereClause)
            ->findOne();
    }
}
