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

namespace tests\integration\Espo\Extension;

use Espo\Core\Upgrades\ExtensionManager;
use Espo\Entities\User;
use integration\Core\NoTransaction;
use tests\integration\Core\BaseTestCase;

class GeneralTest extends BaseTestCase
{
    protected ?string $password = '1';

    protected $packagePath = 'Extension/General.zip';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createUser([
            'type' => User::TYPE_ADMIN,
            'userName' => 'admin',
            'lastName' => 'Admin',
            'password' => $this->password,
        ]);
    }

    protected function beforeSetUp(): void
    {
        $this->fullReset();
    }

    /**
     * If this test fails, an instance may become broken for consecutive test runs.
     */
    #[NoTransaction]
    public function testExtensionUploadInstallUninstallDelete(): void
    {
        $this->authenticate('admin');

        $extensionId = $this->testUninstall();

        $extensionManager = new ExtensionManager($this->getContainer());
        $extensionManager->delete(['id' => $extensionId]);

        $this->assertFileDoesNotExist('data/.backup/extensions/' . $extensionId);
        $this->assertFileDoesNotExist('data/upload/extensions/' . $extensionId);
        $this->assertFileDoesNotExist('data/upload/extensions/' . $extensionId . 'z');

        $this->assertFileDoesNotExist('application/Espo/Modules/Test');
        $this->assertFileDoesNotExist('application/Espo/Modules/Test/Resources/metadata/scopes/TestEntity.json');
        $this->assertFileDoesNotExist('client/modules/test');
        $this->assertFileDoesNotExist('client/modules/test/src/views/test-entity/fields/custom-type.js');

        $this->assertFileExists('vendor/symfony');
        $this->assertFileExists('extension.php');
        $this->assertFileExists('upgrade.php');
    }

    private function testUpload(): string
    {
        $fileData = file_get_contents($this->normalizePath($this->packagePath));
        $fileData = 'data:application/zip;base64,' . base64_encode($fileData);

        $extensionManager = new ExtensionManager($this->getContainer());
        $extensionId = $extensionManager->upload($fileData);

        $this->assertStringMatchesFormat('%x', $extensionId);
        $this->assertFileExists('data/upload/extensions/' . $extensionId . 'z');
        $this->assertFileExists('data/upload/extensions/' . $extensionId);

        return $extensionId;
    }

    private function testInstall(): string
    {
        $extensionId = $this->testUpload();

        $extensionManager = new ExtensionManager($this->getContainer());
        $extensionManager->install(['id' => $extensionId]);

        $this->assertFileExists('data/upload/extensions/' . $extensionId . 'z');
        $this->assertFileDoesNotExist('data/upload/extensions/' . $extensionId);
        $this->assertFileExists('data/.backup/extensions/' . $extensionId);

        $this->assertFileExists('application/Espo/Modules/Test');
        $this->assertFileExists('application/Espo/Modules/Test/Resources/metadata/scopes/TestEntity.json');
        $this->assertFileExists('client/modules/test');
        $this->assertFileExists('client/modules/test/src/views/test-entity/fields/custom-type.js');

        $this->assertFileDoesNotExist('vendor/symfony');
        $this->assertFileDoesNotExist('extension.php');
        $this->assertFileDoesNotExist('upgrade.php');

        return $extensionId;
    }

    private function testUninstall(): string
    {
        $extensionId = $this->testInstall();

        $extensionManager = new ExtensionManager($this->getContainer());
        $extensionManager->uninstall(['id' => $extensionId]);

        $this->assertFileDoesNotExist('data/.backup/extensions/' . $extensionId);
        $this->assertFileDoesNotExist('data/upload/extensions/' . $extensionId);
        $this->assertFileExists('data/upload/extensions/' . $extensionId . 'z');

        $this->assertFileDoesNotExist('application/Espo/Modules/Test');
        $this->assertFileDoesNotExist('application/Espo/Modules/Test/Resources/metadata/scopes/TestEntity.json');
        $this->assertFileDoesNotExist('client/modules/test');
        $this->assertFileDoesNotExist('client/modules/test/src/views/test-entity/fields/custom-type.js');

        $this->assertFileExists('vendor/symfony');
        $this->assertFileExists('extension.php');
        $this->assertFileExists('upgrade.php');

        return $extensionId;
    }
}
