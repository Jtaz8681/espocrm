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

namespace Espo\Tools\User;

use Espo\Entities\Preferences;
use Espo\ORM\EntityManager;
use RuntimeException;

/**
 * @since 9.3.0
 */
class PreferencesProvider
{
    /** @var array<string, ?Preferences> */
    private array $cache = [];

    public function __construct(
        private EntityManager $entityManager,
    ) {}

    public function tryGet(string $userId): ?Preferences
    {
        if (!isset($this->cache[$userId])) {
            $this->cache[$userId] = $this->entityManager
                ->getRepositoryByClass(Preferences::class)
                ->getById($userId);
        }

        return $this->cache[$userId];
    }

    public function get(string $userId): Preferences
    {
        return $this->tryGet($userId) ??
            throw new RuntimeException("Could not get preferences for $userId");
    }
}
