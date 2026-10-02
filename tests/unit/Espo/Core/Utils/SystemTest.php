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

namespace tests\unit\Espo\Core\Utils;

use Espo\Core\Utils\Util;
use Espo\Core\Utils\System;

class SystemTest extends \PHPUnit\Framework\TestCase
{
    protected $object;

    protected $reflection;


    protected function setUp() : void
    {
        $this->object = new \Espo\Core\Utils\System();
    }

    protected function tearDown() : void
    {
        $this->object = NULL;
    }

    public function testGetServerType()
    {
        $_SERVER['SERVER_SOFTWARE'] = 'Apache/2.2.17 (Ubuntu)';
        $this->assertEquals( 'apache', $this->object->getServerType());

        $_SERVER['SERVER_SOFTWARE'] = 'Apache';
        $this->assertEquals( 'apache', $this->object->getServerType());

        $_SERVER['SERVER_SOFTWARE'] = 'nginx/1.5.12';
        $this->assertEquals( 'nginx', $this->object->getServerType());

        $_SERVER['SERVER_SOFTWARE'] = 'Microsoft-IIS/8.0';
        $this->assertEquals( 'microsoft-iis', $this->object->getServerType());

        $_SERVER['SERVER_SOFTWARE'] = 'apache/2.4.10 (win32) openssl/1.0.1i php';
        $this->assertEquals( 'apache', $this->object->getServerType());
    }

    public function testGetOS()
    {
        $possibleValues = array(
            'windows',
            'mac',
            'linux',
        );

        $this->assertTrue( in_array($this->object->getOS(), $possibleValues));
    }

    public function testGetRootDir()
    {
        $rootDir = dirname(__FILE__);
        $rootDir = str_replace(Util::fixPath('/tests/unit/Espo/Core/Utils'),'',$rootDir);

        $this->assertEquals($rootDir, $this->object->getRootDir());
    }

    public function testGetPhpBinary()
    {
        $phpBinary = (new \Symfony\Component\Process\PhpExecutableFinder)->find();

        if (isset($phpBinary)) {
            $this->assertEquals($phpBinary, $this->object->getPhpBinary());
        }
    }

    public function testGetPhpVersion()
    {
        $this->assertTrue( (bool) preg_match('/^[0-9\.]+$/', System::getPhpVersion()) );
    }
}
