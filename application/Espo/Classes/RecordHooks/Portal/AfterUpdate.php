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

namespace Espo\Classes\RecordHooks\Portal;

use Espo\Core\Acl\Cache\Clearer;
use Espo\Core\DataManager;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\Portal;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Repositories\Portal as PortalRepository;

/**
 * @implements SaveHook<Portal>
 */
class AfterUpdate implements SaveHook
{
    public function __construct(
        private Clearer $clearer,
        private DataManager $dataManager,
        private EntityManager $entityManager
    ) {}

    public function process(Entity $entity): void
    {
        $this->getPortalRepository()->loadUrlField($entity);

        if (!$entity->isAttributeChanged('portalRolesIds')) {
            return;
        }

        $this->clearer->clearForAllPortalUsers();
        $this->dataManager->updateCacheTimestamp();
    }

    private function getPortalRepository(): PortalRepository
    {
        /** @var PortalRepository */
        return $this->entityManager->getRDBRepositoryByClass(Portal::class);
    }
}
