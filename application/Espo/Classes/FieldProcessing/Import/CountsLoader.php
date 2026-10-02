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

namespace Espo\Classes\FieldProcessing\Import;

use Espo\Entities\Import;
use Espo\ORM\Entity;
use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\ORM\EntityManager;

use Espo\Repositories\Import as ImportRepository;

/**
 * @implements Loader<Import>
 */
class CountsLoader implements Loader
{
    public function __construct(private EntityManager $entityManager)
    {}

    public function process(Entity $entity, Params $params): void
    {
        /** @var ImportRepository $repository */
        $repository = $this->entityManager->getRepository('Import');

        $importedCount = $repository->countResultRecords($entity, 'imported');
        $duplicateCount = $repository->countResultRecords($entity, 'duplicates');
        $updatedCount = $repository->countResultRecords($entity, 'updated');

        $entity->set([
            'importedCount' => $importedCount,
            'duplicateCount' => $duplicateCount,
            'updatedCount' => $updatedCount,
        ]);
    }
}
