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

namespace Espo\Core\Record;

use Espo\ORM\Collection as OrmCollection;
use Espo\ORM\Entity;
use Espo\ORM\EntityCollection;

use stdClass;

/**
 * Contains an ORM collection and total number of records.
 *
 * @template-covariant TEntity of Entity
 */
class Collection
{
    public const TOTAL_HAS_MORE = -1;
    public const TOTAL_HAS_NO_MORE = -2;

    /**
     * @param OrmCollection<TEntity> $collection
     */
    public function __construct(
        private OrmCollection $collection,
        private ?int $total = null
    ) {}

    /**
     * Get a total number of records in DB (that matches applied search parameters).
     */
    public function getTotal(): ?int
    {
        return $this->total;
    }

    /**
     * Get an ORM collection.
     *
     * @return OrmCollection<TEntity>
     */
    public function getCollection(): OrmCollection
    {
        return $this->collection;
    }

    /**
     * Get a value map list.
     *
     * @return stdClass[]
     */
    public function getValueMapList(): array
    {
        if (
            $this->collection instanceof EntityCollection &&
            !$this->collection->getEntityType()
        ) {
            $list = [];

            foreach ($this->collection as $e) {
                $item = $e->getValueMap();

                $item->_scope = $e->getEntityType();

                $list[] = $item;
            }

            return $list;
        }

        return $this->collection->getValueMapList();
    }

    /**
     * Create.
     *
     * @template CEntity of Entity
     * @param OrmCollection<CEntity> $collection
     * @return self<CEntity>
     */
    public static function create(OrmCollection $collection, ?int $total = null): self
    {
        return new self($collection, $total);
    }

    /**
     * Create w/o count.
     *
     * @template CEntity of Entity
     * @param OrmCollection<CEntity> $collection
     * @return self<CEntity>
     */
    public static function createNoCount(OrmCollection $collection, ?int $maxSize): self
    {
        if (
            $maxSize !== null &&
            $collection instanceof EntityCollection &&
            count($collection) > $maxSize
        ) {
            $copyCollection = new EntityCollection([...$collection], $collection->getEntityType());

            unset($copyCollection[count($copyCollection) - 1]);

            return new self($copyCollection, self::TOTAL_HAS_MORE);
        }

        return new self($collection, self::TOTAL_HAS_NO_MORE);
    }

    /**
     * To API output. To be used in API actions.
     *
     * @since 9.1.0
     */
    public function toApiOutput(): stdClass
    {
        return (object) [
            'total' => $this->getTotal(),
            'list' => $this->getValueMapList(),
        ];
    }
}
