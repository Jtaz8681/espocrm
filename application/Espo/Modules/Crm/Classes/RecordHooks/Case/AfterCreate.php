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

namespace Espo\Modules\Crm\Classes\RecordHooks\Case;

use Espo\Core\Acl;
use Espo\Core\Name\Field;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\Email;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Lead;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

use RuntimeException;

/**
 * @implements SaveHook<CaseObj>
 * @noinspection PhpUnused
 */
class AfterCreate implements SaveHook
{
    private const EMAIL_REPLY_LEVEL = 3;
    private const EMAIL_REPLY_LIMIT = 2;
    private const EMAIL_REPLY_LIMIT_SECOND = 1;

    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl,
    ) {}

    public function process(Entity $entity): void
    {
        /** @var ?string $emailId */
        $emailId = $entity->get('originalEmailId');

        if (!$emailId) {
            return;
        }

        $email = $this->entityManager->getRDBRepositoryByClass(Email::class)->getById($emailId);

        if (!$email) {
            return;
        }

        $this->changeEmailParent($email, $entity);
    }

    private function changeEmailParent(Email $email, CaseObj $entity, int $level = 0): void
    {
        if (!$this->acl->checkEntityRead($email)) {
            return;
        }

        if (
            $email->getParentId() &&
            !in_array($email->getParentType(), [
                Account::ENTITY_TYPE,
                Contact::ENTITY_TYPE,
                Lead::ENTITY_TYPE,
            ])
        ) {
            return;
        }

        $email->setParent($entity);
        $this->entityManager->saveEntity($email);

        if ($level === self::EMAIL_REPLY_LEVEL) {
            return;
        }

        $limit = $level === 0 ? self::EMAIL_REPLY_LIMIT : self::EMAIL_REPLY_LIMIT_SECOND;

        $replies = $this->entityManager
            ->getRelation($email, Email::LINK_REPLIES)
            ->limit(0, $limit)
            ->order(Field::CREATED_AT)
            ->find();

        foreach ($replies as $reply) {
            if (!$reply instanceof Email) {
                throw new RuntimeException();
            }

            $this->changeEmailParent($reply, $entity, $level + 1);
        }
    }
}
