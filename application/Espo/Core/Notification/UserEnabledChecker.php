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

namespace Espo\Core\Notification;

use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Config;
use Espo\Entities\Preferences;

class UserEnabledChecker
{
    /** @var array<string, bool> */
    private $assignmentCache = [];

    public function __construct(
        private EntityManager $entityManager,
        private Config $config,
    ) {}

    public function checkAssignment(string $entityType, string $userId): bool
    {
        if (!in_array($entityType, $this->config->get('assignmentNotificationsEntityList', []))) {
            return false;
        }

        $key = $entityType . '_' . $userId;

        if (!array_key_exists($key, $this->assignmentCache)) {
            $preferences = $this->entityManager->getEntityById(Preferences::ENTITY_TYPE, $userId);

            $isEnabled = false;

            $ignoreList = [];

            if ($preferences) {
                $isEnabled = true;

                $ignoreList = $preferences->get('assignmentNotificationsIgnoreEntityTypeList') ?? [];
            }

            if ($preferences && in_array($entityType, $ignoreList)) {
                $isEnabled = false;
            }

            $this->assignmentCache[$key] = $isEnabled;
        }

        return $this->assignmentCache[$key];
    }
}
