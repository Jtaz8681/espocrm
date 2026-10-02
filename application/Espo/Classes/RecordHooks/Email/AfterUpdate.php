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

namespace Espo\Classes\RecordHooks\Email;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Mail\Exceptions\NoSmtp;
use Espo\Core\Mail\Exceptions\SendingError;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\Tools\Email\SendService;

/**
 * @implements SaveHook<Email>
 */
class AfterUpdate implements SaveHook
{
    public function __construct(
        private User $user,
        private SendService $sendService
    ) {}

    /**
     * @throws BadRequest
     * @throws Error
     * @throws NoSmtp
     * @throws SendingError
     */
    public function process(Entity $entity): void
    {
        if ($entity->getStatus() === Email::STATUS_SENDING) {
            $this->sendService->send($entity, $this->user);
        }
    }
}
