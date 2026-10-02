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

namespace Espo\Core\Record\Output;

use Espo\Core\Acl;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\ORM\Entity;

class FilterProvider
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata,
        private Acl $acl,
        private User $user
    ) {}

    /**
     * @return Filter<Entity>[]
     */
    public function get(string $entityType): array
    {
        $classNameList = $this->getClassNameList($entityType);

        $binding = BindingContainerBuilder::create()
            ->bindInstance(User::class, $this->user)
            ->bindInstance(Acl::class, $this->acl)
            ->build();

        return array_map(
            fn ($className) => $this->injectableFactory->createWithBinding($className, $binding),
            $classNameList
        );
    }

    /**
     * @return class-string<Filter<Entity>>[]
     */
    private function getClassNameList(string $entityType): array
    {
        /** @var class-string<Filter<Entity>>[] */
        return [
            ...$this->metadata->get("app.record.outputFilterClassNameList", []),
            ...$this->metadata->get("recordDefs.$entityType.outputFilterClassNameList", []),
        ];
    }
}
