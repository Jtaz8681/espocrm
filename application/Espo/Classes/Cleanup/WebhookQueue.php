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
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DateTime as DateTimeUtil;
use Espo\Entities\WebhookEventQueueItem;
use Espo\Entities\WebhookQueueItem;
use Espo\ORM\EntityManager;
use DateTime;
use Espo\ORM\Name\Attribute;

/**
 * @noinspection PhpUnused
 */
class WebhookQueue implements Cleanup
{
    private string $cleanupWebhookQueuePeriod = '10 days';

    public function __construct(private Config $config, private EntityManager $entityManager)
    {}

    public function process(): void
    {
        $period = '-' . $this->config->get('cleanupWebhookQueuePeriod', $this->cleanupWebhookQueuePeriod);

        $datetime = new DateTime();
        $datetime->modify($period);
        $from = $datetime->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);

        $query1 = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from(WebhookQueueItem::ENTITY_TYPE)
            ->where([
                'DATE:(createdAt)<' => $from,
                'OR' => [
                    'status!=' => WebhookQueueItem::STATUS_PENDING,
                    Attribute::DELETED => true,
                ],
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($query1);

        $query2 = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from(WebhookEventQueueItem::ENTITY_TYPE)
            ->where([
                'DATE:(createdAt)<' => $from,
                'OR' => [
                    'isProcessed' => true,
                    Attribute::DELETED => true,
                ],
            ])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($query2);
    }
}
