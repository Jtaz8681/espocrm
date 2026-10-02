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

namespace Espo\Core\Field;

use InvalidArgumentException;

/**
 * A link-multiple value object. Immutable.
 */
class LinkMultiple
{
    /**
     * @param LinkMultipleItem[] $list
     * @throws InvalidArgumentException
     */
    public function __construct(private array $list = [])
    {
        $this->validateList();
    }

    public function __clone()
    {
        $newList = [];

        foreach ($this->list as $item) {
            $newList[] = clone $item;
        }

        $this->list = $newList;
    }

    /**
     * Whether contains a specific ID.
     */
    public function hasId(string $id): bool
    {
        return $this->searchIdInList($id) !== null;
    }

    /**
     * Get a list of IDs.
     *
     * @return string[]
     */
    public function getIdList(): array
    {
        $idList = [];

        foreach ($this->list as $item) {
            $idList[] = $item->getId();
        }

        return $idList;
    }

    /**
     * Get a list of items.
     *
     * @return LinkMultipleItem[]
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * Get a number of items.
     */
    public function getCount(): int
    {
        return count($this->list);
    }

    /**
     * Get item by ID.
     */
    public function getById(string $id): ?LinkMultipleItem
    {
        foreach ($this->list as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Clone with an added ID.
     */
    public function withAddedId(string $id): self
    {
        return $this->withAdded(LinkMultipleItem::create($id));
    }

    /**
     * Clone with an added IDs.
     *
     * @param string[] $idList IDs.
     */
    public function withAddedIdList(array $idList): self
    {
        $obj = $this;

        foreach ($idList as $id) {
            $obj = $obj->withAddedId($id);
        }

        return $obj;
    }

    /**
     * Clone with an added item.
     */
    public function withAdded(LinkMultipleItem $item): self
    {
        return $this->withAddedList([$item]);
    }

    /**
     * Clone with an added item list.
     * .
     * @param LinkMultipleItem[] $list
     * @throws InvalidArgumentException
     */
    public function withAddedList(array $list): self
    {
        $newList = $this->list;

        foreach ($list as $item) {
            $index = $this->searchIdInList($item->getId());

            if ($index !== null) {
                $newList[$index] = $item;

                continue;
            }

            $newList[] = $item;
        }

        return self::create($newList);
    }

    /**
     * Clone with removed item.
     */
    public function withRemoved(LinkMultipleItem $item): self
    {
        return $this->withRemovedById($item->getId());
    }

    /**
     * Clone with removed item by ID.
     */
    public function withRemovedById(string $id): self
    {
        $newList = $this->list;

        $index = $this->searchIdInList($id);

        if ($index !== null) {
            unset($newList[$index]);

            $newList = array_values($newList);
        }

        return self::create($newList);
    }

    /**
     * Create with an optional item list.
     *
     * @param LinkMultipleItem[] $list
     *
     * @throws InvalidArgumentException
     */
    public static function create(array $list = []): self
    {
        return new self($list);
    }

    private function validateList(): void
    {
        $idList = [];

        foreach ($this->list as $item) {
            if (!$item instanceof LinkMultipleItem) {
                throw new InvalidArgumentException("Bad item.");
            }

            if (in_array($item->getId(), $idList)) {
                throw new InvalidArgumentException("List contains duplicates.");
            }

            $idList[] = strtolower($item->getId());
        }
    }

    private function searchIdInList(string $id): ?int
    {
        foreach ($this->getIdList() as $i => $item) {
            if ($item === $id) {
                return $i;
            }
        }

        return null;
    }
}
