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

namespace Espo\Core\FieldProcessing;

use Espo\Core\InjectableFactory;
use Espo\ORM\Defs;
use Espo\ORM\Entity;
use RuntimeException;

/**
 * @since 9.1.0
 * @internal Yet experimental.
 */
class SpecificFieldLoader
{
    public function __construct(
        private Defs $defs,
        private InjectableFactory $injectableFactory,
    ) {}

    public function process(Entity $entity, string $field): void
    {
        /** @var ?class-string<Loader<Entity>> $loaderClassName */
        $loaderClassName = $this->defs
            ->getEntity($entity->getEntityType())
            ->tryGetField($field)
            ?->getParam('loaderClassName');

        if (!$loaderClassName) {
            return;
        }

        $loader = $this->injectableFactory->create($loaderClassName);

        if (!$loader instanceof Loader) {
            throw new RuntimeException("Bad field loader.");
        }

        $loader->process($entity, Loader\Params::create());
    }
}
