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

namespace Espo\Core\Upgrades\Actions\Upgrade;

use Espo\Core\Exceptions\Error;
use Espo\Core\Upgrades\Migration\Script;
use Espo\Core\Upgrades\Migration\VersionUtil;
use RuntimeException;

class Install extends \Espo\Core\Upgrades\Actions\Base\Install
{
    /**
     * @param array<string, mixed> $data
     * @throws Error
     */
    public function stepBeforeUpgradeScript(array $data): void
    {
        $this->stepBeforeInstallScript($data);
    }

    /**
     * @param array<string, mixed> $data
     * @throws Error
     */
    public function stepAfterUpgradeScript(array $data): void
    {
        $this->stepAfterInstallScript($data);
        $this->runMigrationAfterInstallScript();
    }

    /**
     * @throws Error
     */
    protected function finalize(): void
    {
        $configWriter = $this->createConfigWriter();
        $configWriter->set('version', $this->getTargetVersion());
        $configWriter->save();
    }

    /**
     * Delete temporary package files.
     *
     * @throws Error
     */
    protected function deletePackageFiles(): bool
    {
        $res = parent::deletePackageFiles();
        $res &= $this->deletePackageArchive();

        return (bool) $res;
    }

    /**
     * @throws Error
     */
    private function getTargetVersion(): string
    {
        $version = $this->getManifest()['version'];

        if (!$version) {
            throw new RuntimeException("No 'version' in manifest.");
        }

        return $version;
    }

    /**
     * @throws Error
     */
    private function runMigrationAfterInstallScript(): void
    {
        $targetVersion = $this->getTargetVersion();
        $version = $this->getConfig()->get('version');

        if (!$version || !is_string($version)) {
            throw new RuntimeException("No or bad 'version' in config.");
        }

        $script = $this->getMigrationAfterInstallScript($version, $targetVersion);

        if (!$script) {
            return;
        }

        $script->run();
    }

    private function getMigrationAfterInstallScript(string $version, string $targetVersion): ?Script
    {
        $isPatch = VersionUtil::isPatch($version, $targetVersion);
        $a = VersionUtil::split($targetVersion);

        $dir = $isPatch ?
            'V' . $a[0] . '_' . $a[1] . '_' . $a[2] :
            'V' . $a[0] . '_' . $a[1];

        $className = "Espo\\Core\\Upgrades\\Migrations\\$dir\\AfterUpgrade";

        if (!class_exists($className)) {
            return null;
        }

        $script = $this->getInjectableFactory()->createWith($className, ['isUpgrade' => true]);

        if (!$script instanceof Script) {
            throw new RuntimeException("$className does not implement Script interface.");
        }

        return $script;
    }
}
