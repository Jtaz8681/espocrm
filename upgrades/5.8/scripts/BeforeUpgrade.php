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
    }

    protected function checkDatabaseRequirements()
    {
        $databaseRequirements = [
            'mysql' => '5.7.0',
            'mariadb' => '10.1.0',
        ];

        $databaseHelper = new \Espo\Core\Utils\Database\Helper($this->container->get('config'));
        $pdo = $this->container->get('entityManager')->getPDO();

        $databaseType = $databaseHelper->getDatabaseType();
        $fullVersion = $databaseHelper->getPdoDatabaseVersion($pdo);

        if (preg_match('/[0-9]+\.[0-9]+\.[0-9]+/', $fullVersion, $match)) {
            $version = $match[0];
            $databaseTypeLc = strtolower($databaseType);

            if (isset($databaseRequirements[$databaseTypeLc])) {
                $requiredVersion = $databaseRequirements[$databaseTypeLc];
                if (version_compare($version, $requiredVersion, '<')) {
                    $msg = "Your {$databaseType} version is not supported. Please upgrade {$databaseType} to a newer version ({$requiredVersion} or later).";
                    throw new \Espo\Core\Exceptions\Error($msg);
                }
            }
        }

        $query = "
            ALTER TABLE `user` ADD `middle_name` VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_unicode_ci
        ";

        try {
            $sth = $pdo->prepare($query);
            $sth->execute();
        } catch (\Exception $e) {}


        $query = "
            UPDATE email_account SET port = NULL WHERE port = ''
        ";
        try {
            $sth = $pdo->prepare($query);
            $sth->execute();
        } catch (\Exception $e) {}

        $query = "
            UPDATE inbound_email SET port = NULL WHERE port = ''
        ";
        try {
            $sth = $pdo->prepare($query);
            $sth->execute();
        } catch (\Exception $e) {}
    }
}
