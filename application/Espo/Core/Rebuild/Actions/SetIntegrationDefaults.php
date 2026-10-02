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

namespace Espo\Core\Rebuild\Actions;

use Espo\Core\Rebuild\RebuildAction;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Integration;
use Espo\ORM\EntityManager;

/**
 * @noinspection PhpUnused
 */
class SetIntegrationDefaults implements RebuildAction
{
    public function __construct(
        private Metadata $metadata,
        private EntityManager $entityManager,
    ) {}

    public function process(): void
    {
        /** @var string[] $integrations */
        $integrations = array_keys($this->metadata->get('integrations') ?? []);

        foreach ($integrations as $integration) {
            $this->processItem($integration);
        }
    }

    private function processItem(string $name): void
    {
        $integration = $this->entityManager
            ->getRDBRepositoryByClass(Integration::class)
            ->getById($name);

        if (!$integration || !$integration->isEnabled()) {
            return;
        }

        /** @var array<string, array<string, mixed>> $fields */
        $fields = $this->metadata->get("integrations.$name.fields") ?? [];

        foreach ($fields as $field => $defs) {
            $default = $defs['default'] ?? null;

            if ($default === null) {
                continue;
            }

            if ($integration->has($field)) {
                continue;
            }

            $integration->set($field, $default);
        }

        $this->entityManager->saveEntity($integration);
    }
}
