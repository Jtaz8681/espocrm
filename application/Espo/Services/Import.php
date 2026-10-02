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

namespace Espo\Services;

use Espo\Repositories\Import as Repository;
use Espo\Entities\Import as ImportEntity;
use Espo\Core\Acl\Table;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFoundSilent;
use Espo\Core\FieldProcessing\ListLoadProcessor;
use Espo\Core\Record\Collection as RecordCollection;
use Espo\Core\Select\SearchParams;

/**
 * @extends Record<ImportEntity>
 */
class Import extends Record
{
    public function findLinked(string $id, string $link, SearchParams $searchParams): RecordCollection
    {
        if (!in_array($link, ['imported', 'duplicates', 'updated'])) {
            return parent::findLinked($id, $link, $searchParams);
        }

        /** @var ?ImportEntity $entity */
        $entity = $this->getImportRepository()->getById($id);

        if (!$entity) {
            throw new NotFoundSilent();
        }

        $foreignEntityType = $entity->get('entityType');

        if (!$this->acl->check($entity, Table::ACTION_READ)) {
            throw new Forbidden();
        }

        if (!$this->acl->check($foreignEntityType, Table::ACTION_READ)) {
            throw new Forbidden();
        }

        $query = $this->selectBuilderFactory
            ->create()
            ->from($foreignEntityType)
            ->withStrictAccessControl()
            ->withSearchParams($searchParams)
            ->build();

        $collection = $this->getImportRepository()->findResultRecords($entity, $link, $query);

        $listLoadProcessor = $this->injectableFactory->create(ListLoadProcessor::class);

        $recordService = $this->recordServiceContainer->get($foreignEntityType);

        foreach ($collection as $e) {
            $listLoadProcessor->process($e);
            $recordService->prepareEntityForOutput($e);
        }

        $total = $this->getImportRepository()->countResultRecords($entity, $link, $query);

        return new RecordCollection($collection, $total);
    }

    private function getImportRepository(): Repository
    {
        /** @var Repository */
        return $this->getRepository();
    }
}
