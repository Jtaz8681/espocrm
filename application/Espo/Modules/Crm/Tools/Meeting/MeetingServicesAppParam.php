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

use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\Tools\App\AppParam;

/**
 * @noinspection PhpUnused
 */
class MeetingServicesAppParam implements AppParam
{
    public function __construct(
        private Metadata $metadata,
        private User $user,
        private MeetingServiceAvailabilityCheckerFactory $factory,
    ) {}

    /**
     * @return array<int, mixed>
     */
    public function get(): array
    {
        $output = [];

        /** @var array<string, array<string, mixed>> $services */
        $services = $this->metadata->get("app.meetingServices") ?? [];

        foreach ($services as $name => $item) {
            $enabled = $item['enabled'] ?? false;

            if (!$enabled) {
                continue;
            }

            $checker = $this->factory->create($name);

            if (!$checker->check($this->user)) {
                continue;
            }

            $output[] = [
                'name' => $name
            ];
        }

        return $output;
    }
}
