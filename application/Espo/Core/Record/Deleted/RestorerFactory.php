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

namespace Espo\Core\Record\Deleted;

use Espo\Core\Binding\BindingContainer;
use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\ORM\Entity;

class RestorerFactory
{
    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory,
        private User $user,
    ) {}

    public function create(string $entityType): Restorer
    {
        /** @var class-string<Restorer<Entity>> $restorerClassName */
        $restorerClassName = $this->metadata->get("recordDefs.$entityType.deletedRestorerClassName") ??
            DefaultRestorer::class;

        /** @var Restorer */
        return $this->injectableFactory->createWithBinding($restorerClassName, $this->createBinding());
    }

    private function createBinding(): BindingContainer
    {
        return BindingContainerBuilder::create()
            ->bindInstance(User::class, $this->user)
            ->build();
    }
}
