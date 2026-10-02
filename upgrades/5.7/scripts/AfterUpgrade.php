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

class AfterUpgrade
{
    public function run($container)
    {
        $entityManager = $container->get('entityManager');

        $entityManager->createEntity('ScheduledJob', [
            'job' => 'ProcessWebhookQueue',
            'name' => 'Process Webhook Queue',
            'scheduling' => '*/5 * * * *',
            'status' => 'Active',
        ]);

        $config = $container->get('config');

        $config->set('hashSecretKey', \Espo\Core\Utils\Util::generateApiKey());
        $config->save();
    }
}
