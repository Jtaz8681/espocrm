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

namespace Espo\Core\Utils\Address;

use Espo\Core\Name\Field;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DataCache;
use Espo\Entities\AddressCountry;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Order;

class CountryDataProvider
{
    /** @var ?array{list: string[], preferredList: string[]} */
    private ?array $data = null;
    private bool $useCache;

    private const CACHE_KEY = 'addressCountryData';
    private const LIMIT = 500;

    public function __construct(
        private DataCache $dataCache,
        private EntityManager $entityManager,
        Config\SystemConfig $systemConfig,
    ) {
        $this->useCache = $systemConfig->useCache();
    }

    /**
     * @return array{list: string[], preferredList: string[]}
     */
    public function get(): array
    {
        if ($this->data === null) {
            $this->data = $this->load();
        }

        return $this->data;
    }

    /**
     * @return array{list: string[], preferredList: string[]}
     */
    private function load(): array
    {
        if ($this->useCache && $this->dataCache->has(self::CACHE_KEY)) {
            $list = $this->dataCache->get(self::CACHE_KEY);

            if (
                is_array($list) &&
                is_array($list['list'] ?? null) &&
                is_array($list['preferredList'] ?? null)
            ) {
                /** @var array{list: string[], preferredList: string[]} */
                return $list;
            }
        }

        $list = [];
        $preferredList = [];

        /** @var iterable<AddressCountry> $collection */
        $collection = $this->entityManager
            ->getRDBRepositoryByClass(AddressCountry::class)
            ->sth()
            ->select([Field::NAME, 'isPreferred'])
            ->order(Field::NAME, Order::ASC)
            ->limit(0, self::LIMIT)
            ->find();

        foreach ($collection as $entity) {
            $list[] = $entity->getName();

            if ($entity->isPreferred()) {
                $preferredList[] = $entity->getName();
            }
        }

        if ($this->useCache) {
            $this->dataCache->store(self::CACHE_KEY, [
                'list' => $list,
                'preferredList' => $preferredList,
            ]);
        }

        return [
            'list' => $list,
            'preferredList' => $preferredList,
        ];
    }
}
