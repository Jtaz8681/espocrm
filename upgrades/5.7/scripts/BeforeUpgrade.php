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

class BeforeUpgrade
{
    public function run($container)
    {
        $this->container = $container;
        $this->checkDatabaseRequirements();

        $pdo = $container->get('entityManager')->getPDO();

        try {
            $pdo->query("TRUNCATE TABLE `scheduled_job_log_record`");
        } catch (\Exception $e) {}
    }

    protected function checkDatabaseRequirements()
    {
        $databaseRequirements = [
            'mysql' => '5.6.0',
            'mariadb' => '10.0.0',
        ];

        $databaseHelper = new \Espo\Core\Utils\Database\Helper($this->container->get('config'));

        $databaseType = $databaseHelper->getDatabaseType();
        $fullVersion = $databaseHelper->getPdoDatabaseVersion($this->container->get('entityManager')->getPDO());

        if (preg_match('/[0-9]+\.[0-9]+\.[0-9]+/', $fullVersion, $match)) {
            $version = $match[0];
            $databaseTypeLc = strtolower($databaseType);

            if (isset($databaseRequirements[$databaseTypeLc])) {
                if (version_compare($version, $databaseRequirements[$databaseTypeLc], '<')) {
                    $msg = "Your {$databaseType} version is not supported. Please upgrade {$databaseType} to a newer version (5.6 or later).";
                    throw new \Espo\Core\Exceptions\Error($msg);
                }
            }
        }
    }
}
