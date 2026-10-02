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

namespace Espo\Core\ORM\QueryComposer;

use Espo\Core\Binding\BindingContainerBuilder;
use Espo\Core\InjectableFactory;
use Espo\ORM\EventDispatcher;
use Espo\ORM\QueryComposer\Part\FunctionConverterFactory;
use Espo\Core\Utils\Metadata;
use Espo\ORM\PDO\PDOProvider;
use Espo\ORM\QueryComposer\QueryComposer;
use Espo\ORM\Metadata as OrmMetadata;
use Espo\ORM\EntityFactory;

use PDO;
use RuntimeException;

class QueryComposerFactory implements \Espo\ORM\QueryComposer\QueryComposerFactory
{
    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory,
        private PDOProvider $pdoProvider,
        private OrmMetadata $ormMetadata,
        private EntityFactory $entityFactory,
        private FunctionConverterFactory $functionConverterFactory,
        private EventDispatcher $eventDispatcher
    ) {}

    public function create(string $platform): QueryComposer
    {
        /** @var ?class-string<QueryComposer> $className */
        $className =
            $this->metadata->get(['app', 'orm', 'platforms', $platform, 'queryComposerClassName']) ??
            $this->metadata->get(['app', 'orm', 'queryComposerClassNameMap', $platform]);

        if (!$className) {
            /** @var class-string<QueryComposer> $className */
            $className = "Espo\\ORM\\QueryComposer\\{$platform}QueryComposer";
        }

        if (!class_exists($className)) {
            throw new RuntimeException("Query composer for '{$platform}' platform does not exist.");
        }

        $bindingContainer = BindingContainerBuilder::create()
            ->bindInstance(PDO::class, $this->pdoProvider->get())
            ->bindInstance(OrmMetadata::class, $this->ormMetadata)
            ->bindInstance(EntityFactory::class, $this->entityFactory)
            ->bindInstance(FunctionConverterFactory::class, $this->functionConverterFactory)
            ->bindInstance(EventDispatcher::class, $this->eventDispatcher)
            ->build();

        return $this->injectableFactory->createWithBinding($className, $bindingContainer);
    }
}
