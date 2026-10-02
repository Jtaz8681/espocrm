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
use Espo\Core\Name\Field;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Entities\StreamSubscription;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Condition as Cond;

class Subscribers implements Cleanup
{
    private const PERIOD = '2 months';

    public function __construct(
        private Metadata $metadata,
        private EntityManager $entityManager,
        private Config $config
    ) {}

    public function process(): void
    {
        if (!$this->config->get('cleanupSubscribers')) {
            return;
        }

        /** @var string[] $scopeList */
        $scopeList = array_keys($this->metadata->get(['scopes']) ?? []);

        /** @var string[] $scopeList */
        $scopeList = array_values(array_filter(
            $scopeList,
            fn ($item) => (bool) $this->metadata->get(['scopes', $item, 'stream'])
        ));

        foreach ($scopeList as $scope) {
            $this->processEntityType($scope);
        }
    }

    private function processEntityType(string $entityType): void
    {
        /** @var ?array<string, mixed> $data */
        $data = $this->metadata->get(['streamDefs', $entityType, 'subscribersCleanup']);

        if (!($data['enabled'] ?? false)) {
            return;
        }

        /** @var string $dateField */
        $dateField = $data['dateField'] ?? Field::CREATED_AT;
        /** @var ?string[] $statusList */
        $statusList = $data['statusList'] ?? null;
        /** @var ?string $statusField */
        $statusField = $this->metadata->get(['scopes', $entityType, 'statusField']);

        if ($statusList === null || $statusField === null) {
            return;
        }

        /** @var string $period */
        $period = $this->metadata->get(['streamDefs', $entityType, 'subscribersCleanup', 'period']) ??
            $this->config->get('cleanupSubscribersPeriod') ??
            self::PERIOD;

        $before = DateTime::createNow()->modify('-' . $period);

        $query = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from(StreamSubscription::ENTITY_TYPE, 'subscription')
            ->join(
                $entityType,
                'entity',
                Cond::equal(
                    Cond::column('entity.id'),
                    Cond::column('entityId')
                )
            )
            ->where(
                Cond::and(
                    Cond::equal(
                        Cond::column('entityType'),
                        $entityType
                    ),
                    Cond::less(
                        Cond::column('entity.' . $dateField),
                        $before->toString()
                    ),
                    Cond::in(
                        Cond::column('entity.' . $statusField),
                        $statusList
                    )
                )
            )
            ->build();

        $this->entityManager->getQueryExecutor()->execute($query);
    }
}
