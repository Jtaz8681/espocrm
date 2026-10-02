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

namespace Espo\Core\Utils\ScheduledJob;

use Espo\Core\Utils\Language;
use Espo\Core\Utils\Metadata;
use Espo\Entities\ScheduledJob;
use Espo\ORM\EntityManager;

/**
 * @internal
 * @since 10.0.0
 */
class Populator
{
    public function __construct(
        private EntityManager $entityManager,
        private Metadata $metadata,
        private Language $defaultLanguage,
    ) {}

    /**
     * @return string[]
     */
    public function populate(): array
    {
        $output = [];

        /** @var array<string, array{isDefault?: bool, scheduling?: string}> $defs */
        $defs = $this->metadata->get('app.scheduledJobs', []);

        foreach ($defs as $name => $def) {
            $scheduling = $def['scheduling'] ?? null;
            $idDefault = $def['isDefault'] ?? false;

            if (!$idDefault || !$scheduling) {
                continue;
            }

            $created = $this->createIsNotExists($name, $scheduling);

            if ($created) {
                $output[] = $name;
            }
        }

        return $output;
    }

    private function createIsNotExists(string $name, string $scheduling): bool
    {
        if ($this->exists($name)) {
            return false;
        }

        $recordName = $this->defaultLanguage->translateOption($name, 'job', ScheduledJob::ENTITY_TYPE);

        $entity = $this->entityManager->getRDBRepositoryByClass(ScheduledJob::class)->getNew();

        $entity
            ->setJob($name)
            ->setActive()
            ->setScheduling($scheduling)
            ->setName($recordName);

        $this->entityManager->saveEntity($entity);

        return true;
    }

    private function exists(string $name): bool
    {
        $one = $this->entityManager->getRDBRepositoryByClass(ScheduledJob::class)
            ->where([
                ScheduledJob::FIELD_JOB => $name,
            ])
            ->findOne();

        return $one !== null;
    }
}
