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

namespace Espo\Core\Record\Defaults;

use Espo\Core\Acl;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\ORM\Entity;

class PopulatorFactory
{
    /** @var class-string<DefaultPopulator> */
    private string $defaultClassName = DefaultPopulator::class;

    public function __construct(
        private InjectableFactory $injectableFactory,
        private Metadata $metadata,
        private User $user,
        private Acl $acl
    ) {}

    /**
     * @return Populator<Entity>
     */
    public function create(string $entityType): Populator
    {
        $binding = BindingContainerBuilder::create()
            ->bindInstance(User::class, $this->user)
            ->bindInstance(Acl::class, $this->acl)
            ->build();

        return $this->injectableFactory->createWithBinding($this->getClassName($entityType), $binding);
    }

    /**
     * @return class-string<Populator<Entity>>
     */
    private function getClassName(string $entityType): string
    {
        /** @var ?class-string<Populator<Entity>> $className */
        $className = $this->metadata->get("recordDefs.$entityType.defaultsPopulatorClassName");

        if ($className) {
            return $className;
        }

        return $this->defaultClassName;
    }
}
