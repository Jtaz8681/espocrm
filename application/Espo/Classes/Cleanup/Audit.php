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

namespace Espo\Classes\Cleanup;

use Espo\Core\Cleanup\Cleanup;
use Espo\Core\Field\DateTime;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Note;
use Espo\ORM\EntityManager;

/**
 * @noinspection PhpUnused
 */
class Audit implements Cleanup
{
    private const PERIOD = '3 months';

    public function __construct(
        private Metadata $metadata,
        private EntityManager $entityManager,
        private Config $config
    ) {}

    public function process(): void
    {
        if (!$this->config->get('cleanupAudit')) {
            return;
        }

        $entityTypeList = $this->getEntityTypeList();

        foreach ($entityTypeList as $scope) {
            $this->processEntityType($scope);
        }
    }

    private function processEntityType(string $entityType): void
    {
        $query = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from(Note::ENTITY_TYPE)
            ->where([
                'parentType' => $entityType,
                'createdAt<' => $this->getBefore()->toString(),
                'type' => [Note::TYPE_UPDATE],
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($query);
    }

    /**
     * @return string[]
     */
    private function getEntityTypeList(): array
    {
        /** @var string[] $scopeList */
        $scopeList = array_keys($this->metadata->get(['scopes']) ?? []);

        $scopeList = array_filter($scopeList, function ($item) {
            return $this->metadata->get("scopes.$item.entity") &&
                !$this->metadata->get("scopes.$item.preserveAuditLog") &&
                !$this->metadata->get("scopes.$item.stream");
        });

        return array_values($scopeList);
    }

    private function getBefore(): DateTime
    {
        /** @var string $period */
        $period = $this->config->get('cleanupAuditPeriod') ?? self::PERIOD;

        return DateTime::createNow()->modify('-' . $period);
    }
}
