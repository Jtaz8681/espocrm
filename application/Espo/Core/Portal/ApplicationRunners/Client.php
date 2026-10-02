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

namespace Espo\Core\Portal\ApplicationRunners;

use Espo\Core\Application\Runner;
use Espo\Core\ApplicationState;
use Espo\Core\Utils\ClientManager;

/**
 * Displays the main HTML page for a portal.
 */
class Client implements Runner
{
    public function __construct(
        private ClientManager $clientManager,
        private ApplicationState $applicationState
    ) {}

    public function run(): void
    {
        $portalId = $this->applicationState->getPortal()->getId();

        $this->clientManager->display(null, null, [
            'portalId' => $portalId,
            'applicationId' => $portalId,
            'apiUrl' => 'api/v1/portal-access/' . $portalId,
            'appClientClassName' => 'app-portal',
        ]);
    }
}
