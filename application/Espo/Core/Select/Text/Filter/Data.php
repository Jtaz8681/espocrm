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

namespace Espo\Core\Select\Text\Filter;

use Espo\ORM\Query\Part\WhereItem;

/**
 * Immutable.
 */
class Data
{
    private string $filter;
    /** @var string[] */
    private array $attributeList;
    private bool $skipWildcards = false;
    private ?WhereItem $fullTextSearchWhereItem = null;
    private bool $forceFullTextSearch = false;

    /**
     * @param string[] $attributeList
     */
    public function __construct(string $filter, array $attributeList)
    {
        $this->filter = $filter;
        $this->attributeList = $attributeList;
    }

    /**
     * @param string[] $attributeList
     */
    public static function create(string $filter, array $attributeList): self
    {
        return new self($filter, $attributeList);
    }

    public function withFilter(string $filter): self
    {
        $obj = clone $this;
        $obj->filter = $filter;

        return $obj;
    }

    /**
     * @param string[] $attributeList
     */
    public function withAttributeList(array $attributeList): self
    {
        $obj = clone $this;
        $obj->attributeList = $attributeList;

        return $obj;
    }

    public function withSkipWildcards(bool $skipWildcards = true): self
    {
        $obj = clone $this;
        $obj->skipWildcards = $skipWildcards;

        return $obj;
    }

    public function withForceFullTextSearch(bool $forceFullTextSearch = true): self
    {
        $obj = clone $this;
        $obj->forceFullTextSearch = $forceFullTextSearch;

        return $obj;
    }

    public function withFullTextSearchWhereItem(?WhereItem $fullTextSearchWhereItem): self
    {
        $obj = clone $this;
        $obj->fullTextSearchWhereItem = $fullTextSearchWhereItem;

        return $obj;
    }

    public function getFilter(): string
    {
        return $this->filter;
    }

    /**
     * @return string[]
     */
    public function getAttributeList(): array
    {
        return $this->attributeList;
    }

    public function skipWildcards(): bool
    {
        return $this->skipWildcards;
    }

    public function forceFullTextSearch(): bool
    {
        return $this->forceFullTextSearch;
    }

    public function getFullTextSearchWhereItem(): ?WhereItem
    {
        return $this->fullTextSearchWhereItem;
    }
}
