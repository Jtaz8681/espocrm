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

namespace Espo\Core\Select\Applier\Appliers;

use Espo\Core\Binding\ContextualBinder;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use Espo\Core\Select\SearchParams;
use Espo\Core\InjectableFactory;
use Espo\Core\Select\Applier\AdditionalApplier;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Entities\User;

class Additional
{
    public function __construct(
        private User $user,
        private InjectableFactory $injectableFactory,
        private string $entityType,
        private Metadata $metadata,
    ) {}

    /**
     * @param class-string<AdditionalApplier>[] $classNameList
     */
    public function apply(array $classNameList, QueryBuilder $queryBuilder, SearchParams $searchParams): void
    {
        $classNameList = array_merge($this->getMandatoryClassNameList(), $classNameList);

        foreach ($classNameList as $className) {
            $applier = $this->createApplier($className);

            $applier->apply($queryBuilder, $searchParams);
        }
    }

    /**
     * @param class-string<AdditionalApplier> $className
     */
    private function createApplier(string $className): AdditionalApplier
    {
        return $this->injectableFactory->createWithBinding(
            $className,
            BindingContainerBuilder::create()
                ->bindInstance(User::class, $this->user)
                ->inContext($className, function (ContextualBinder $binder) {
                    $binder->bindValue('$entityType', $this->entityType);
                })
                ->build()
        );
    }

    /**
     * @return class-string<AdditionalApplier>[]
     */
    private function getMandatoryClassNameList(): array
    {
        /** @var class-string<AdditionalApplier>[] */
        return $this->metadata->get("selectDefs.$this->entityType.additionalApplierClassNameList") ?? [];
    }
}
