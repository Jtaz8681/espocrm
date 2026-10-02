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
        $this->container = $container;

        $this->processEmailAccountsUpdate();

        $this->cleanupFiles();
    }

    protected function processEmailAccountsUpdate()
    {
        $pdo = $this->container->get('entityManager')->getPDO();

        $q1 = "UPDATE `email_account` SET email_account.security = '' WHERE email_account.ssl = 0";
        $q2 = "UPDATE `inbound_email` SET inbound_email.security = '' WHERE inbound_email.ssl = 0";

        $pdo->exec($q1);
        $pdo->exec($q2);
    }

    protected function cleanupFiles()
    {
        $fileManager = $this->container->get('fileManager');

        $fileList = [
            'application/Espo/Core/Loaders/ClientManager.php',
            'client/res/templates/record/panels/default-side.tpl',
            'application/Espo/Resources/templates/noteEmailRecieved',
        ];

        foreach ($fileList as $file) {
            if (is_dir($file)) {
                $fileManager->removeInDir($file, true);

                continue;
            }

            if (!file_exists($file)) {
                continue;
            }

            unlink($file);
        }
    }
}
