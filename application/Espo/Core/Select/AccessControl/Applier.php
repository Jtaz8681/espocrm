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

namespace Espo\Core\Select\AccessControl;

use Espo\Core\Select\AccessControl\FilterFactory as AccessControlFilterFactory;
use Espo\Core\Select\AccessControl\FilterResolverFactory as AccessControlFilterResolverFactory;
use Espo\Entities\User;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use RuntimeException;

class Applier
{
    public function __construct(
        private string $entityType,
        private User $user,
        private AccessControlFilterFactory $accessControlFilterFactory,
        private AccessControlFilterResolverFactory $accessControlFilterResolverFactory,
    ) {}

    public function apply(QueryBuilder $queryBuilder): void
    {
        $this->applyMandatoryFilter($queryBuilder);

        $accessControlFilterResolver = $this->accessControlFilterResolverFactory
            ->create($this->entityType, $this->user);

        $filterName = $accessControlFilterResolver->resolve();

        if (!$filterName) {
            return;
        }

        if ($this->accessControlFilterFactory->has($this->entityType, $filterName)) {
            $filter = $this->accessControlFilterFactory
                ->create($this->entityType, $this->user, $filterName);

            $filter->apply($queryBuilder);

            return;
        }

        throw new RuntimeException("No access filter '{$filterName}' for '{$this->entityType}'.");
    }

    private function applyMandatoryFilter(QueryBuilder $queryBuilder): void
    {
        $filter = $this->accessControlFilterFactory
            ->create($this->entityType, $this->user, 'mandatory');

        $filter->apply($queryBuilder);
    }
}
