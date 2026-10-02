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

namespace Espo\Modules\Crm\Classes\RecordHooks\Contact;

use Espo\Core\Acl;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Utils\Config;
use Espo\Entities\Email;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @implements SaveHook<Contact>
 */
class AfterCreate implements SaveHook
{
    public function __construct(
        private EntityManager $entityManager,
        private Config $config,
        private Acl $acl
    ) {}

    public function process(Entity $entity): void
    {
        $emailId = $entity->get('originalEmailId');

        if (!$emailId) {
            return;
        }

        /** @var ?Email $email */
        $email = $this->entityManager->getEntityById(Email::ENTITY_TYPE, $emailId);

        if (!$email || $email->getParentId() || !$this->acl->check($email)) {
            return;
        }

        if ($this->config->get('b2cMode') || !$entity->getAccount()) {
            $email->set([
                'parentType' => Contact::ENTITY_TYPE,
                'parentId' => $entity->getId(),
            ]);
        } else if ($entity->getAccount()) {
            $email->set([
                'parentType' => Account::ENTITY_TYPE,
                'parentId' => $entity->getAccount()->getId(),
            ]);
        }

        $this->entityManager->saveEntity($email);
    }
}
