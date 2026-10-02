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

namespace Espo\Modules\Crm\Classes\RecordHooks\Task;

use Espo\Core\Acl;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\Email;
use Espo\Modules\Crm\Entities\Task;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @implements SaveHook<Task>
 */
class BeforeCreate implements SaveHook
{
    public function __construct(
        private Acl $acl,
        private EntityManager $entityManager,
    ) {}

    public function process(Entity $entity): void
    {
        /** @var ?string $emailId */
        $emailId = $entity->get('originalEmailId');

        if ($emailId === null) {
            return;
        }

        $email = $this->entityManager->getRDBRepositoryByClass(Email::class)->getById($emailId);

        if (!$email) {
            throw new BadRequest("Email record not found.");
        }

        if (!$this->acl->checkEntityRead($entity)) {
            throw new Forbidden("No access to email.");
        }

        // As the record hook is processed after the LinkCheck, no forbidden error will be produced.
        $entity->set('emailId', $emailId);
    }
}
