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

namespace Espo\Modules\Crm\Classes\AclPortal\Contact;

use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Contact;
use Espo\ORM\Entity;
use Espo\Core\Portal\Acl\DefaultOwnershipChecker;
use Espo\Core\Portal\Acl\OwnershipAccountChecker;
use Espo\Core\Portal\Acl\OwnershipContactChecker;

/**
 * @implements OwnershipAccountChecker<Contact>
 * @implements OwnershipContactChecker<Contact>
 */
class OwnershipChecker implements OwnershipAccountChecker, OwnershipContactChecker
{
    public function __construct(private DefaultOwnershipChecker $defaultOwnershipChecker) {}

    public function checkContact(User $user, Entity $entity): bool
    {
        $contactId = $user->get('contactId');

        if ($contactId) {
            if ($entity->getId() === $contactId) {
                return true;
            }
        }

        return false;
    }

    public function checkAccount(User $user, Entity $entity): bool
    {
        return $this->defaultOwnershipChecker->checkAccount($user, $entity);
    }
}
