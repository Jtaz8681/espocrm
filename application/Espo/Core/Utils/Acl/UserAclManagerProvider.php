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

namespace Espo\Core\Utils\Acl;

use Espo\Core\Acl\Exceptions\NotAvailable;
use Espo\Entities\Portal;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Espo\Core\AclManager;
use Espo\Core\Portal\AclManagerContainer as PortalAclManagerContainer;
use Espo\Core\ApplicationState;

/**
 * @todo Use WeakMap (User as a key).
 */
class UserAclManagerProvider
{
    /** @var array<string, AclManager> */
    private $map = [];

    public function __construct(
        private EntityManager $entityManager,
        private AclManager $aclManager,
        private PortalAclManagerContainer $portalAclManagerContainer,
        private ApplicationState $applicationState
    ) {}

    /**
     * @throws NotAvailable
     */
    public function get(User $user): AclManager
    {
        $key = $user->hasId() ? $user->getId() : spl_object_hash($user);

        if (!isset($this->map[$key])) {
            $this->map[$key] = $this->load($user);
        }

        return $this->map[$key];
    }

    /**
     * @throws NotAvailable
     */
    private function load(User $user): AclManager
    {
        $aclManager = $this->aclManager;

        if ($user->isPortal() && !$this->applicationState->isPortal()) {
            /** @var ?Portal $portal */
            $portal = $this->entityManager
                ->getRDBRepository(User::ENTITY_TYPE)
                ->getRelation($user, 'portals')
                ->findOne();

            if (!$portal) {
                throw new NotAvailable("No portal for portal user '" . $user->getId() . "'.");
            }

            $aclManager = $this->portalAclManagerContainer->get($portal);
        }

        return $aclManager;
    }
}
