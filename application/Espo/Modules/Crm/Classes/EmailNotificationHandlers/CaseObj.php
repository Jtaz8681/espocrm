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

namespace Espo\Modules\Crm\Classes\EmailNotificationHandlers;

use Espo\Core\Notification\EmailNotificationHandler;

use Espo\Core\Mail\SenderParams;
use Espo\Entities\InboundEmail;
use Espo\ORM\Entity;
use Espo\Entities\User;
use Espo\Entities\Email;

use Espo\ORM\EntityManager;

class CaseObj implements EmailNotificationHandler
{
    /**
     * @var array<string,\Espo\Entities\InboundEmail|null>
     */
    private $inboundEmailEntityHash = [];

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function prepareEmail(Email $email, Entity $entity, User $user): void {}

    public function getSenderParams(Entity $entity, User $user): ?SenderParams
    {
        /** @var ?string $inboundEmailId */
        $inboundEmailId = $entity->get('inboundEmailId');

        if (!$inboundEmailId) {
            return null;
        }

        if (!array_key_exists($inboundEmailId, $this->inboundEmailEntityHash)) {
            $this->inboundEmailEntityHash[$inboundEmailId] =
                $this->entityManager->getEntityById(InboundEmail::ENTITY_TYPE, $inboundEmailId);
        }

        $inboundEmail = $this->inboundEmailEntityHash[$inboundEmailId];

        if (!$inboundEmail) {
            return null;
        }

        $emailAddress = $inboundEmail->get('emailAddress');

        if (!$emailAddress) {
            return null;
        }

        return SenderParams::create()->withReplyToAddress($emailAddress);
    }
}
