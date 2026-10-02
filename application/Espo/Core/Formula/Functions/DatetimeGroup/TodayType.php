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

namespace Espo\Core\Formula\Functions\DatetimeGroup;

use DateTimeZone;
use Espo\Core\Field\DateTime;
use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Func;
use Espo\Core\Utils\Config\ApplicationConfig;
use Espo\Core\Utils\DateTime as DateTimeUtil;
use Exception;
use RuntimeException;

/**
 * @noinspection PhpUnused
 */
class TodayType implements Func
{
    public function __construct(
        private ApplicationConfig $applicationConfig
    ) {}

    public function process(EvaluatedArgumentList $arguments): string
    {
        $timezone = $this->applicationConfig->getTimeZone();

        try {
            $today = DateTime::createNow()->withTimezone(new DateTimeZone($timezone));
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        return $today->toDateTime()->format(DateTimeUtil::SYSTEM_DATE_FORMAT);
    }
}
