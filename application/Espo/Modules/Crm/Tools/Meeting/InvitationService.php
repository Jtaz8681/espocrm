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

namespace Espo\Modules\Crm\Tools\Meeting;

use Espo\Core\Acl;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Mail\Exceptions\SendingError;
use Espo\Core\Record\ServiceContainer as RecordServiceContainer;
use Espo\Modules\Crm\Entities\Call;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\Modules\Crm\Tools\Meeting\Invitation\Sender;
use Espo\Modules\Crm\Tools\Meeting\Invitation\Invitee;
use Espo\ORM\Entity;

class InvitationService
{
    private const TYPE_INVITATION = 'invitation';
    private const TYPE_CANCELLATION = 'cancellation';

    public function __construct(
        private RecordServiceContainer $recordServiceContainer,
        private Acl $acl,
        private Sender $invitationSender,
    ) {}

    /**
     * Send invitation emails for a meeting (or call). Checks access. Uses user's SMTP if available.
     *
     * @param ?Invitee[] $targets
     * @return Entity[] Entities an invitation was sent to.
     * @throws NotFound
     * @throws Forbidden
     * @throws Error
     * @throws SendingError
     */
    public function send(string $entityType, string $id, ?array $targets = null): array
    {
        return $this->sendInternal($entityType, $id, $targets, self::TYPE_INVITATION);
    }

    /**
     * Send cancellation emails for a meeting (or call). Checks access. Uses user's SMTP if available.
     *
     * @param ?Invitee[] $targets
     * @return Entity[] Entities a cancellation was sent to.
     * @throws NotFound
     * @throws Forbidden
     * @throws Error
     * @throws SendingError
     */
    public function sendCancellation(string $entityType, string $id, ?array $targets = null): array
    {
        return $this->sendInternal($entityType, $id, $targets, self::TYPE_CANCELLATION);
    }

    /**
     * @param ?Invitee[] $targets
     * @return Entity[]
     * @throws NotFound
     * @throws Forbidden
     * @throws Error
     * @throws SendingError
     */
    private function sendInternal(
        string $entityType,
        string $id,
        ?array $targets,
        string $type,
    ): array {

        $entity = $this->recordServiceContainer
            ->get($entityType)
            ->getEntity($id);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden("No edit access.");
        }

        if (!$entity instanceof Meeting && !$entity instanceof Call) {
            throw new Error("Not supported entity type.");
        }

        if ($type === self::TYPE_CANCELLATION) {
            return $this->invitationSender->sendCancellation($entity, $targets);
        }

        return $this->invitationSender->sendInvitation($entity, $targets);
    }
}
