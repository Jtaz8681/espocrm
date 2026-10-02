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

namespace Espo\Classes\Acl\Email\LinkCheckers;

use Espo\Core\Acl\LinkChecker;
use Espo\Core\AclManager;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\ORM\Entity;

/**
 * @implements LinkChecker<Email, Entity>
 * @noinspection PhpUnused
 */
class ParentLinkChecker implements LinkChecker
{
    public function __construct(
        private AclManager $aclManager
    ) {}

    public function check(User $user, Entity $entity, Entity $foreignEntity): bool
    {
        if ($this->aclManager->checkEntityRead($user, $foreignEntity)) {
            return true;
        }

        $replied = $entity->getReplied();

        if (!$replied) {
            return false;
        }

        $parent = $replied->getParent();

        if (
            !$parent ||
            $parent->getId() !== $foreignEntity->getId() ||
            $parent->getEntityType() !== $foreignEntity->getEntityType()
        ) {
            return false;
        }

        return $this->aclManager->checkEntityRead($user, $replied);
    }
}
