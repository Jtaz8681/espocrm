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

namespace Espo\Modules\Crm\Classes\RecordHooks\Meeting;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Meeting;
use Espo\Modules\Crm\Tools\Meeting\MeetingServiceAvailabilityCheckerFactory;
use Espo\ORM\Entity;

/**
 * @implements SaveHook<Meeting>
 */
class BeforeCreateExternalServiceCheck implements SaveHook
{
    public function __construct(
        private Metadata $metadata,
        private MeetingServiceAvailabilityCheckerFactory $factory,
        private User $user,
    ) {}

    public function process(Entity $entity): void
    {
        $service = $entity->getExternalService();

        if (!$service) {
            return;
        }

        if (!$this->metadata->get("app.meetingServices.$service.enabled")) {
            throw new BadRequest("Not supported service '$service'.");
        }

        $checker = $this->factory->create($service);

        if (!$checker->check($this->user)) {
            throw new Forbidden("Not allowed service '$service'.");
        }
    }
}
