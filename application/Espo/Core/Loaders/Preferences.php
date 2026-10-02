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

namespace Espo\Core\Loaders;

use Espo\Core\ApplicationState;
use Espo\Core\Container\Loader;
use Espo\Core\ORM\EntityManager;

use Espo\Core\Utils\SystemUser;
use Espo\Entities\Preferences as PreferencesEntity;

class Preferences implements Loader
{
    public function __construct(
        private EntityManager $entityManager,
        private ApplicationState $applicationState,
        private SystemUser $systemUser
    ) {}

    public function load(): PreferencesEntity
    {
        $id = $this->applicationState->hasUser() ?
            $this->applicationState->getUser()->getId() :
            $this->systemUser->getId();

        /** @var PreferencesEntity */
        return $this->entityManager->getEntityById(PreferencesEntity::ENTITY_TYPE, $id);
    }
}
