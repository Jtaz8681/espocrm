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

use Espo\ORM\EntityManager;

use Espo\Entities\TwoFactorCode;

use DateTime;

class TwoFactorCodes implements Cleanup
{
    private const PERIOD = '5 days';

    private $config;

    private $entityManager;

    public function __construct(Config $config, EntityManager $entityManager)
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
    }

    public function process(): void
    {
        $period = '-' . $this->config->get('cleanupTwoFactorCodesPeriod', self::PERIOD);

        $from = (new DateTime())
            ->modify($period)
            ->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);

        $query = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from(TwoFactorCode::ENTITY_TYPE)
            ->where([
                'createdAt<' => $from,
            ])
            ->build();

        $this->entityManager
            ->getQueryExecutor()
            ->execute($query);
    }
}
