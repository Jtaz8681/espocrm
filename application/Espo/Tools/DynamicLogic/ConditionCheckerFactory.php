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

namespace Espo\Tools\DynamicLogic;

use DateTimeZone;
use Espo\Core\Utils\Config\ApplicationConfig;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Tools\DynamicLogic\ConditionChecker\Options;
use Exception;
use RuntimeException;

/**
 * @since 9.1.0
 * @noinspection PhpUnused
 */
class ConditionCheckerFactory
{
    public function __construct(
        private User $user,
        private ApplicationConfig $applicationConfig,
    ) {}

    /**
     * @param Entity $entity An entity to check.
     */
    public function create(Entity $entity): ConditionChecker
    {
        try {
            $timezone = new DateTimeZone($this->applicationConfig->getTimeZone());
        } catch (Exception $e) {
            throw new RuntimeException('', 0, $e);
        }

        return new ConditionChecker(
            entity: $entity,
            user: $this->user,
            options: new Options(
                timezone: $timezone,
            ),
        );
    }
}
