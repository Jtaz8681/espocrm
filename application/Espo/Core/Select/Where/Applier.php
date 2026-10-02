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

namespace Espo\Core\Select\Where;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Select\Where\Item as WhereItem;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use Espo\Entities\User;

class Applier
{
    public function __construct(
        private string $entityType,
        private User $user,
        private ConverterFactory $converterFactory,
        private CheckerFactory $checkerFactory
    ) {}

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function apply(QueryBuilder $queryBuilder, WhereItem $whereItem, Params $params): void
    {
        $this->check($whereItem, $params);

        $converter = $this->converterFactory->create($this->entityType, $this->user);

        $convertedParams = new Converter\Params(useSubQueryIfMany: true);

        $queryBuilder->where(
            $converter->convert($queryBuilder, $whereItem, $convertedParams)
        );
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    private function check(Item $whereItem, Params $params): void
    {
        $checker = $this->checkerFactory->create($this->entityType, $this->user);

        $checker->check($whereItem, $params);
    }
}
