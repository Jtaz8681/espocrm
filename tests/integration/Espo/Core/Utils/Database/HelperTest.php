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

namespace tests\integration\Espo\Core\Utils\Database;

use Espo\Core\Utils\Database\Helper;
use Doctrine\DBAL\Connection;
use PDO;
use tests\integration\Core\BaseTestCase;

class HelperTest extends BaseTestCase
{
    /** @var ?Helper */
    protected $helper;

    protected function initTest()
    {
        $this->helper = $this->getInjectableFactory()->create(Helper::class);
    }

    private function getDatabaseInfo()
    {
        $pdo = $this->getEntityManager()->getPDO();

        $sth = $pdo->prepare("select version()");
        $sth->execute();

        $version = $sth->fetchColumn();

        $type = 'mysql';
        if (preg_match('/mariadb/i', $version)) {
            $type = 'mariadb';
        }

        if (preg_match('/[0-9]+\.[0-9]+\.[0-9]+/', $version, $match)) {
            $version = $match[0];
        }

        return [
            'type' => $type,
            'version' => $version,
        ];
    }

    public function testGetDbalConnectionWithConfig()
    {
        $this->initTest();

        $this->assertInstanceOf(Connection::class, $this->helper->getDbalConnection());
    }

    public function testGetPdoConnectionWithConfig()
    {
        $this->initTest();

        $this->assertInstanceOf(PDO::class, $this->helper->getPDO());
    }

    public function testGetDatabaseInfo()
    {
        $this->initTest();

        $databaseInfo = $this->getDatabaseInfo();
        if (empty($databaseInfo)) {
            return;
        }

        $this->assertEquals($databaseInfo['type'], strtolower($this->helper->getType()));
        $this->assertEquals($databaseInfo['version'], $this->helper->getVersion());
    }

    public function testGetDatabaseType()
    {
        $this->initTest();

        $databaseInfo = $this->getDatabaseInfo();
        if (empty($databaseInfo)) {
            return;
        }

        switch ($databaseInfo['type']) {
            case 'mysql':
                $this->assertEquals('MySQL', $this->helper->getType());
                break;

            case 'mariadb':
                $this->assertEquals('MariaDB', $this->helper->getType());
                break;
        }
    }
}
