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

namespace Espo\Modules\Crm\Classes\Acl\Case\LinkCheckers;

use Espo\Core\Acl\LinkChecker;
use Espo\Core\AclManager;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @implements LinkChecker<CaseObj, Account>
 * @noinspection PhpUnused
 */
class AccountLinkChecker implements LinkChecker
{
    public function __construct(
        private AclManager $aclManager,
        private EntityManager $entityManager
    ) {}

    public function check(User $user, Entity $entity, Entity $foreignEntity): bool
    {
        if ($this->aclManager->checkEntityRead($user, $foreignEntity)) {
            return true;
        }

        if (!$entity->isNew()) {
            return false;
        }

        $emailId = $entity->get('originalEmailId');

        if (!$emailId) {
            return false;
        }

        $email = $this->entityManager->getRepositoryByClass(Email::class)->getById($emailId);

        if (!$email) {
            return false;
        }

        $parent = $email->getParent();

        if (!$parent) {
            return false;
        }

        if (
            $parent->getEntityType() !== Account::ENTITY_TYPE ||
            $parent->getId() !== $foreignEntity->getId()
        ) {
            return false;
        }

        return $this->aclManager->checkEntityRead($user, $email);
    }
}
