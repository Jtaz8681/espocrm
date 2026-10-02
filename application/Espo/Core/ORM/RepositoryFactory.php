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

namespace Espo\Core\ORM;

use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\Binding\ContextualBinder;
use Espo\Core\InjectableFactory;
use Espo\ORM\Entity as Entity;
use Espo\ORM\EntityFactory as EntityFactoryInterface;
use Espo\ORM\Relation\RelationsMap;
use Espo\ORM\Repository\Repository;
use Espo\ORM\Repository\RepositoryFactory as RepositoryFactoryInterface;

class RepositoryFactory implements RepositoryFactoryInterface
{
    public function __construct(
        private EntityFactoryInterface $entityFactory,
        private InjectableFactory $injectableFactory,
        private ClassNameProvider $classNameProvider,
        private RelationsMap $relationsMap,
    ) {}

    public function create(string $entityType): Repository
    {
        $className = $this->getClassName($entityType);

        return $this->injectableFactory->createWithBinding(
            $className,
            BindingContainerBuilder::create()
                ->bindInstance(EntityFactoryInterface::class, $this->entityFactory)
                ->bindInstance(EntityFactory::class, $this->entityFactory)
                ->bindInstance(RelationsMap::class, $this->relationsMap)
                ->inContext(
                    $className,
                    function (ContextualBinder $binder) use ($entityType) {
                        $binder->bindValue('$entityType', $entityType);
                    }
                )
                ->build()
        );
    }

    /**
     * @return class-string<Repository<Entity>>
     */
    private function getClassName(string $entityType): string
    {
        /** @var class-string<Repository<Entity>> */
        return $this->classNameProvider->getRepositoryClassName($entityType);
    }
}
