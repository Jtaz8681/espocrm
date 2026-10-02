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

namespace Espo\Core\Record\Input;

use Espo\Core\Acl;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\Binding\ContextualBinder;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;

class FilterProvider
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata,
        private Acl $acl,
        private User $user
    ) {}

    /**
     * @return Filter[]
     */
    public function getForCreate(string $entityType): array
    {
        $list = [];

        foreach ($this->getCreateClassNameList($entityType) as $className) {
            $list[] = $this->createFilter($className, $entityType);
        }

        return $list;
    }

    /**
     * @return Filter[]
     */
    public function getForUpdate(string $entityType): array
    {
        $list = [];

        foreach ($this->getUpdateClassNameList($entityType) as $className) {
            $list[] = $this->createFilter($className, $entityType);
        }

        return $list;
    }

    /**
     * @param class-string<Filter> $className
     */
    private function createFilter(string $className, string $entityType): Filter
    {
        $binding = BindingContainerBuilder::create()
            ->bindInstance(User::class, $this->user)
            ->bindInstance(Acl::class, $this->acl)
            ->inContext($className, function (ContextualBinder $binder) use ($entityType) {
                $binder->bindValue('$entityType', $entityType);
            })
            ->build();

        return $this->injectableFactory->createWithBinding($className, $binding);
    }

    /**
     * @return class-string<Filter>[]
     */
    private function getCreateClassNameList(string $entityType): array
    {
        /** @var class-string<Filter>[] */
        return [
            ...$this->metadata->get("app.record.createInputFilterClassNameList", []),
            ...$this->metadata->get("recordDefs.$entityType.createInputFilterClassNameList", [])
        ];
    }

    /**
     * @return class-string<Filter>[]
     */
    private function getUpdateClassNameList(string $entityType): array
    {
        /** @var class-string<Filter>[] */
        return [
            ...$this->metadata->get("app.record.updateInputFilterClassNameList", []),
            ...$this->metadata->get("recordDefs.$entityType.updateInputFilterClassNameList", [])
        ];
    }
}
