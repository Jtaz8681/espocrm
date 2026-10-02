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

use Espo\Core\Exceptions\Error;

class BeforeUpgrade
{
    public function run($container)
    {
        $this->container = $container;

        $this->processCheckCLI();

        $this->processCheckExtensions();

        $this->processMyIsamCheck();

        $this->processNextNumberAlterTable();
    }

    protected function processCheckCLI()
    {
        $isCli = (substr(php_sapi_name(), 0, 3) == 'cli') ? true : false;

        if (!$isCli) {
            throw new Error("This upgrade can be run only from CLI.");
        }
    }

    protected function processCheckExtensions()
    {
        $em = $this->container->get('entityManager');

        $extension = $em->getRepository('Extension')
            ->where([
                'name' => 'Google Integration',
                'isInstalled' => true,
            ])
            ->findOne();

        if ($extension) {
            $version = $extension->get('version');

            if (version_compare($version, '1.4.2', '<')) {
                $message =
                    "BugZyro 6.0.0 is not compatible with Google Integration extension of a version lower than 1.4.2. " .
                    "Please upgrade the extension or uninstall it. Then run the upgrade command again.";

                throw new Error($message);
            }
        }

        $extension = $em->getRepository('Extension')
            ->where([
                'name' => 'Real Estate',
                'isInstalled' => true,
            ])
            ->findOne();

        if ($extension) {
            $version = $extension->get('version');

            if (version_compare($version, '1.4.0', '<')) {
                $message =
                    "BugZyro 6.0.0 is not compatible with Real Estate extension of a version lower than 1.4.0. " .
                    "Please upgrade the extension or uninstall it. Then run the upgrade command again.";

                throw new Error($message);
            }
        }

        $extension = $em->getRepository('Extension')
            ->where([
                'name' => 'VoIP Integration',
                'isInstalled' => true,
            ])
            ->findOne();

        if ($extension) {
            $version = $extension->get('version');

            if (version_compare($version, '1.15.0', '<')) {
                $message =
                    "BugZyro 6.0.0 is not compatible with VoIP Integration extension of a version lower than 1.15.0. " .
                    "Please upgrade the extension or uninstall it. Then run the upgrade command again.";

                throw new Error($message);
            }
        }
    }

    protected function processMyIsamCheck()
    {
        $myisamTableList = $this->getMyIsamTableList();

        if (empty($myisamTableList)) {
            return;
        }

        $isCli = (substr(php_sapi_name(), 0, 3) == 'cli') ? true : false;

        $tableListString = implode(", ", $myisamTableList);

        $lineBreak = $isCli ? "\n" : "<br>";

        $link = "#";

        $linkString = $isCli ? $link : "<a href=\"{$link}\" target=\"_blank\">link</a>";

        $message =
            "In v6.0 we have dropped a support of MyISAM engine for DB tables. " .
            "You have the following tables that use MyISAM: {$tableListString}.{$lineBreak}{$lineBreak}" .
            "Please change the engine to InnoDB for these tables then run upgrade again.{$lineBreak}{$lineBreak}" .
            "See: {$linkString}.";

        throw new Error($message);
    }

    protected function getMyIsamTableList()
    {
        $container = $this->container;

        $pdo = $container->get('entityManager')->getPDO();
        $databaseInfo = $container->get('config')->get('database');

        try {
            $sth = $pdo->prepare("
                SELECT TABLE_NAME as tableName
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = '". $databaseInfo['dbname'] ."'
                AND ENGINE = 'MyISAM'
            ");

            $sth->execute();
        }
        catch (Exception $e) {
            return [];
        }

        $tableList = $sth->fetchAll(PDO::FETCH_COLUMN);

        if (empty($tableList)) {
            return [];
        }

        return $tableList;
    }

    protected function processNextNumberAlterTable()
    {
        $pdo = $this->container->get('entityManager')->getPDO();

        $q1 = "ALTER TABLE `next_number` CHANGE `entity_type` `entity_type` VARCHAR(100)";
        $q2 = "ALTER TABLE `next_number` CHANGE `field_name` `field_name` VARCHAR(100)";

        $pdo->exec($q1);
        $pdo->exec($q2);
    }
}
