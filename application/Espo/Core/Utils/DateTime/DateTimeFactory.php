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

namespace Espo\Core\Utils\DateTime;

use Espo\Core\Utils\Config\ApplicationConfig;
use Espo\Core\Utils\DateTime;
use Espo\Core\InjectableFactory;
use Espo\ORM\EntityManager;
use Espo\Entities\User;
use Espo\Entities\Preferences;

class DateTimeFactory
{
    public function __construct(
        private InjectableFactory $injectableFactory,
        private EntityManager $entityManager,
        private ApplicationConfig $applicationConfig,
    ) {}

    public function createWithUserTimeZone(User $user): DateTime
    {
        $preferences = $this->entityManager->getEntityById(Preferences::ENTITY_TYPE, $user->getId());

        $timeZone = $this->applicationConfig->getTimeZone();

        if ($preferences) {
            $timeZone = $preferences->get('timeZone') ? $preferences->get('timeZone') : $timeZone;
        }

        return $this->createWithTimeZone($timeZone);
    }

    public function createWithTimeZone(string $timeZone): DateTime
    {
        return $this->injectableFactory->createWith(DateTime::class, [
            'timeZone' => $timeZone,
            'dateFormat' => $this->applicationConfig->getDateFormat(),
            'timeFormat' => $this->applicationConfig->getTimeFormat(),
            'language' => $this->applicationConfig->getLanguage(),
        ]);
    }
}
