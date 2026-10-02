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

namespace Espo\ORM\Defs;

use Espo\ORM\Metadata;

use RuntimeException;

class DefsData
{
    /** @var array<string, ?EntityDefs> */
    private array $cache = [];

    public function __construct(private Metadata $metadata)
    {}

    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * @return string[]
     */
    public function getEntityTypeList(): array
    {
        return $this->metadata->getEntityTypeList();
    }

    public function hasEntity(string $name): bool
    {
        $this->cacheEntity($name);

        return !is_null($this->cache[$name]);
    }

    public function getEntity(string $name): EntityDefs
    {
        $this->cacheEntity($name);

        if (!$this->hasEntity($name)) {
            throw new RuntimeException("Entity type '{$name}' does not exist.");
        }

        /** @var EntityDefs */
        return $this->cache[$name];
    }

    private function cacheEntity(string $name): void
    {
        if (array_key_exists($name, $this->cache)) {
            return;
        }

        $this->cache[$name] = $this->loadEntity($name);
    }

    private function loadEntity(string $name): ?EntityDefs
    {
        $raw = $this->metadata->get($name) ?? null;

        if (!$raw) {
            return null;
        }

        return EntityDefs::fromRaw($raw, $name);
    }
}
