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
use Espo\Entities\AppLogRecord;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\DeleteBuilder;

class AppLog implements Cleanup
{
    private const PERIOD = '30 days';

    public function __construct(
        private EntityManager $entityManager,
        private Config $config
    ) {}

    public function process(): void
    {
        if (!$this->config->get('cleanupAppLog')) {
            return;
        }

        $query = DeleteBuilder::create()
            ->from(AppLogRecord::ENTITY_TYPE)
            ->where(['createdAt<' => $this->getBefore()->toString()])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($query);
    }

    private function getBefore(): DateTime
    {
        /** @var string $period */
        $period = $this->config->get('cleanupAppLogPeriod') ?? self::PERIOD;

        return DateTime::createNow()->modify('-' . $period);
    }
}
