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

namespace Espo\Core\Portal;

use Espo\ORM\Entity;

use Espo\Entities\User;

use Espo\Core\Acl as BaseAcl;

class Acl extends BaseAcl
{
    public function __construct(AclManager $aclManager, User $user)
    {
        parent::__construct($aclManager, $user);
    }

    /**
     * Whether 'read' access is set to 'account' for a specific scope.
     */
    public function checkReadOnlyAccount(string $scope): bool
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->aclManager;

        return $aclManager->checkReadOnlyAccount($this->user, $scope);
    }

    /**
     * Whether 'read' access is set to 'contact' for a specific scope.
     */
    public function checkReadOnlyContact(string $scope): bool
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->aclManager;

        return $aclManager->checkReadOnlyContact($this->user, $scope);
    }

    /**
     * Check whether an entity belongs to a user account.
     */
    public function checkOwnershipAccount(Entity $entity): bool
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->aclManager;

        return $aclManager->checkOwnershipAccount($this->user, $entity);
    }

    /**
     * Check whether an entity belongs to a user contact.
     */
    public function checkOwnershipContact(Entity $entity): bool
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->aclManager;

        return $aclManager->checkOwnershipContact($this->user, $entity);
    }

    /**
     * @deprecate
     */
    public function checkInAccount(Entity $entity): bool
    {
        return $this->checkOwnershipAccount($entity);
    }

    /**
     * @deprecate
     */
    public function checkIsOwnContact(Entity $entity): bool
    {
        return $this->checkOwnershipContact($entity);
    }
}
