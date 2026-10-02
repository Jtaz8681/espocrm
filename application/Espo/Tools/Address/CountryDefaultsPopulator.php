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

namespace Espo\Tools\Address;

use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\File\Manager;
use Espo\Core\Utils\Id\RecordIdGenerator;
use Espo\Core\Utils\Json;
use Espo\Entities\AddressCountry;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\DeleteBuilder;
use RuntimeException;

class CountryDefaultsPopulator
{
    private string $file = 'application/Espo/Resources/data/locale/en_US/countryList.json';

    private const CACHE_KEY = 'addressCountryData';

    public function __construct(
        private Manager $fileManager,
        private EntityManager $entityManager,
        private DataCache $dataCache,
        private RecordIdGenerator $recordIdGenerator
    ) {}

    public function populate(): void
    {
        if (!$this->fileManager->exists($this->file)) {
            throw new RuntimeException("No file '$this->file'.");
        }

        $contents = $this->fileManager->getContents($this->file);

        $dataList = Json::decode($contents, true);

        if (!is_array($dataList)) {
            throw new RuntimeException("Bad data.");
        }

        $collection = $this->entityManager->getCollectionFactory()->create(AddressCountry::ENTITY_TYPE);

        foreach ($dataList as $data) {
            if (!is_array($data)) {
                throw new RuntimeException("Bad data.");
            }

            $name = $data['name'] ?? null;
            $code = $data['code'] ?? null;
            $isPreferred = $data['isPreferred'] ?? false;

            if (!is_string($name) || !is_string($code)) {
                throw new RuntimeException("Bad data.");
            }

            $entity = $this->entityManager->getNewEntity(AddressCountry::ENTITY_TYPE);

            $entity->setMultiple([
                'id' => $this->recordIdGenerator->generate(),
                'name' => $name,
                'code' => $code,
                'isPreferred' => $isPreferred,
            ]);

            $collection->append($entity);
        }

        $this->entityManager->getQueryExecutor()->execute(
            DeleteBuilder::create()
                ->from(AddressCountry::ENTITY_TYPE)
                ->build()
        );

        $this->entityManager->getMapper()->massInsert($collection);

        $this->dataCache->clear(self::CACHE_KEY);
    }
}
