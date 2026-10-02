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

namespace Espo\Services;

use Espo\Core\Record\CreateResult;
use Espo\Tools\Email\SendService;
use Espo\Entities\Email as EmailEntity;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Mail\Exceptions\SendingError;
use Espo\Core\Record\CreateParams;
use stdClass;

/**
 * @extends Record<EmailEntity>
 */
class Email extends Record
{
    protected bool $getEntityBeforeUpdate = true;

    private ?SendService $sendService = null;

    private function getSendService(): SendService
    {
        if (!$this->sendService) {
            $this->sendService = $this->injectableFactory->create(SendService::class);
        }

        return $this->sendService;
    }

    /**
     * @todo Move to hook? Make sure needed data is loaded before sending.
     *
     * @throws BadRequest
     * @throws Error
     * @throws Forbidden
     * @throws Conflict
     * @throws BadRequest
     * @throws SendingError
     */
    public function create(stdClass $data, CreateParams $params = new CreateParams()): CreateResult
    {
        $result = parent::create($data, $params);

        if ($result->getEntity()->getStatus() === EmailEntity::STATUS_SENDING) {
            $this->getSendService()->send($result->getEntity(), $this->user);
        }

        return $result;
    }
}
