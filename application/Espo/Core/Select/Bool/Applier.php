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

namespace Espo\Core\Select\Bool;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Select\Bool\FilterFactory as BoolFilterFactory;
use Espo\ORM\Query\Select;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;

use Espo\Entities\User;

class Applier
{
    public function __construct(
        private string $entityType,
        private User $user,
        private BoolFilterFactory $boolFilterFactory,
    ) {}

    /**
     * @param string[] $boolFilterNameList
     * @throws BadRequest
     */
    public function apply(SelectBuilder $queryBuilder, array $boolFilterNameList): void
    {
        $orGroupBuilder = new OrGroupBuilder();

        $isMultiple = count($boolFilterNameList) > 1;

        if ($isMultiple) {
            $queryBefore = $queryBuilder->build();
        }

        foreach ($boolFilterNameList as $filterName) {
            $this->applyBoolFilter($queryBuilder, $orGroupBuilder, $filterName);
        }

        if ($isMultiple) {
            $this->handleMultiple($queryBefore, $queryBuilder);
        }

        $queryBuilder->where(
            $orGroupBuilder->build()
        );
    }

    /**
     * @throws BadRequest
     */
    private function applyBoolFilter(
        SelectBuilder $queryBuilder,
        OrGroupBuilder $orGroupBuilder,
        string $filterName
    ): void {

        if ($this->boolFilterFactory->has($this->entityType, $filterName)) {
            $filter = $this->boolFilterFactory->create($this->entityType, $this->user, $filterName);

            $filter->apply($queryBuilder, $orGroupBuilder);

            return;
        }

        throw new BadRequest("No bool filter '$filterName' for '$this->entityType'.");
    }

    private function handleMultiple(Select $queryBefore, SelectBuilder $queryBuilder): void
    {
        $queryAfter = $queryBuilder->build();

        $joinCountBefore = count($queryBefore->getJoins());
        $joinCountAfter = count($queryAfter->getJoins());

        if ($joinCountBefore < $joinCountAfter) {
            $queryBuilder->distinct();
        }
    }
}
