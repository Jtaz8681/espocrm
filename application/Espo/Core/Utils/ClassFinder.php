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

namespace Espo\Core\Utils;

use Espo\Core\Utils\File\ClassMap;

/**
 * Finds classes of a specific category. Category examples: Services, Controllers.
 * First it checks in the `custom` folder, then modules, then the internal folder.
 * Available as 'classFinder' service.
 */
class ClassFinder
{

    /** @var array<string, array<string, class-string>> */
    private $dataHashMap = [];

    public function __construct(private ClassMap $classMap)
    {}

    /**
     * Reset runtime cache.
     *
     * @internal
     * @since 8.4.0
     */
    public function resetRuntimeCache(): void
    {
        $this->dataHashMap = [];
    }

    /**
     * Find class name by a category and name.
     *
     * @return ?class-string
     */
    public function find(string $category, string $name, bool $subDirs = false): ?string
    {
        $map = $this->getMap($category, $subDirs);

        return $map[$name] ?? null;
    }

    /**
     * Get a name => class name map.
     *
     * @return array<string, class-string>
     */
    public function getMap(string $category, bool $subDirs = false): array
    {
        if (!array_key_exists($category, $this->dataHashMap)) {
            $this->load($category, $subDirs);
        }

        return $this->dataHashMap[$category] ?? [];
    }

    private function load(string $category, bool $subDirs = false): void
    {
        $cacheFile = $this->buildCacheKey($category);

        $this->dataHashMap[$category] = $this->classMap->getData($category, $cacheFile, null, $subDirs);
    }

    private function buildCacheKey(string $category): string
    {
        return 'classmap' . str_replace('/', '', $category);
    }
}
