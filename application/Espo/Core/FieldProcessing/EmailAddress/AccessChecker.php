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

namespace Espo\Core\FieldProcessing\EmailAddress;

use Espo\Repositories\EmailAddress as Repository;
use Espo\ORM\Entity;

use Espo\Entities\EmailAddress;
use Espo\Entities\User;

use Espo\Core\AclManager;
use Espo\Core\ORM\EntityManager;

class AccessChecker
{
    public function __construct(
        private EntityManager $entityManager,
        private AclManager $aclManager
    ) {}

    public function checkEdit(User $user, EmailAddress $emailAddress, Entity $excludeEntity): bool
    {
        /** @var Repository $repository */
        $repository = $this->entityManager->getRepository('EmailAddress');

        $entityWithSameAddressList = $repository->getEntityListByAddressId($emailAddress->getId(), $excludeEntity);

        foreach ($entityWithSameAddressList as $e) {
            if ($this->aclManager->checkEntityEdit($user, $e)) {
                continue;
            }

            if (
                $e instanceof User &&
                $e->isPortal() &&
                $excludeEntity->getEntityType() === 'Contact' &&
                $e->get('contactId') === $excludeEntity->getEntityType()
            ) {
                continue;
            }

            return false;
        }

        return true;
    }
}
