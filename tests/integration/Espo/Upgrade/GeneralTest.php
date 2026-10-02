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

namespace tests\integration\Espo\Upgrade;

use Espo\Core\Upgrades\UpgradeManager;

class GeneralTest extends \tests\integration\Core\BaseTestCase
{
    protected ?string $dataFile = 'InitData.php';

    protected ?string $userName = 'admin';
    protected ?string $password = '1';

    protected $packagePath = 'Upgrade/General.zip';

    public function testUpload()
    {
        $fileData = file_get_contents($this->normalizePath($this->packagePath));
        $fileData = 'data:application/zip;base64,' . base64_encode($fileData);

        $upgradeManager = new UpgradeManager($this->getContainer());
        $upgradeId = $upgradeManager->upload($fileData);

        $this->assertStringMatchesFormat('%x', $upgradeId);
        $this->assertFileExists('data/upload/upgrades/' . $upgradeId . 'z');
        $this->assertFileExists('data/upload/upgrades/' . $upgradeId);
        //$this->assertDirectoryExists('data/upload/upgrades/' . $upgradeId);

        return $upgradeId;
    }

    public function testInstall()
    {
        $upgradeId = $this->testUpload();

        $upgradeManager = new UpgradeManager($this->getContainer());

        $upgradeManager->install(['id' => $upgradeId]);

        $this->assertFileDoesNotExist('data/upload/upgrades/' . $upgradeId . 'z');
        $this->assertFileDoesNotExist('data/upload/upgrades/' . $upgradeId);
        $this->assertFileExists('data/.backup/upgrades/' . $upgradeId);

        $this->assertFileExists('custom/Espo/Custom/test.php');
        $this->assertFileDoesNotExist('vendor/zendframework');
        $this->assertFileDoesNotExist('extension.php');
        $this->assertFileDoesNotExist('upgrade.php');

        return $upgradeId;
    }

    public function testUninstall()
    {
        $this->expectException('Espo\\Core\\Exceptions\\Error');

        $upgradeId = $this->testInstall();

        $upgradeManager = new UpgradeManager($this->getContainer());
        $upgradeManager->uninstall(array('id' => $upgradeId));
    }

    public function testDelete()
    {
        $this->expectException('Espo\\Core\\Exceptions\\Error');

        $upgradeId = $this->testInstall();

        $upgradeManager = new UpgradeManager($this->getContainer());
        $upgradeManager->delete(['id' => $upgradeId]);
    }
}
