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

namespace tests\unit\Espo\Core\Upgrades\Migration;

use Espo\Core\Console\IO;
use Espo\Core\DataManager;
use Espo\Core\Upgrades\Migration\ExtractedStepsProvider;
use Espo\Core\Upgrades\Migration\Runner;
use Espo\Core\Upgrades\Migration\StepRunner;
use Espo\Core\Upgrades\Migration\VersionDataProvider;
use Espo\Core\Utils\Config\ConfigWriter;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    protected ?IO $io = null;

    protected ?ExtractedStepsProvider $stepsProvider = null;
    protected ?VersionDataProvider $versionDataProvider;
    protected ?StepRunner $stepsRunner = null;
    protected ?DataManager $dataManager = null;
    protected ?ConfigWriter $configWriter = null;

    protected function setUp(): void
    {
        $this->io = $this->createMock(IO::class);

        $this->stepsProvider = $this->createMock(ExtractedStepsProvider::class);
        $this->versionDataProvider = $this->createMock(VersionDataProvider::class);
        $this->stepsRunner = $this->createMock(StepRunner::class);
        $this->dataManager = $this->createMock(DataManager::class);
        $this->configWriter = $this->createMock(ConfigWriter::class);
    }

    public function testRunMigration(): void
    {
        $this->versionDataProvider
            ->expects($this->once())
            ->method('getTargetVersion')
            ->willReturn('8.3.5');

        $this->versionDataProvider
            ->expects($this->once())
            ->method('getPreviousVersion')
            ->willReturn('8.0.4');

        $this->stepsProvider
            ->expects($this->once())
            ->method('getPrepare')
            ->willReturn([
                '8.2',
                '8.3',
            ]);

        $this->stepsProvider
            ->expects($this->once())
            ->method('getAfterUpgrade')
            ->willReturn([
                '8.1',
                '8.3',
            ]);

        $this->stepsRunner
            ->expects($this->any())
            ->method('runPrepare')
            ->willReturnMap([
                ['8.2', true],
                ['8.3', true],
            ]);

        $this->stepsRunner
            ->expects($this->any())
            ->method('runAfterUpgrade')
            ->willReturnMap([
                ['8.1', true],
                ['8.3', true],
            ]);

        $runner = new Runner(
            $this->stepsProvider,
            $this->versionDataProvider,
            $this->stepsRunner,
            $this->dataManager,
            $this->configWriter
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $runner->run($this->io);
    }

    public function testRunNoMigration(): void
    {
        $this->versionDataProvider
            ->expects($this->once())
            ->method('getTargetVersion')
            ->willReturn('9.3.4');

        $this->versionDataProvider
            ->expects($this->once())
            ->method('getPreviousVersion')
            ->willReturn('9.2.7');


        $this->stepsRunner
            ->expects($this->never())
            ->method('runPrepare');

        $this->stepsRunner
            ->expects($this->never())
            ->method('runAfterUpgrade');

        $this->configWriter
            ->expects($this->once())
            ->method('set')
            ->with('version', '9.3.4');

        $runner = new Runner(
            $this->stepsProvider,
            $this->versionDataProvider,
            $this->stepsRunner,
            $this->dataManager,
            $this->configWriter
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $runner->run($this->io);
    }

    public function testRunSameVersion(): void
    {
        $this->versionDataProvider
            ->expects($this->once())
            ->method('getTargetVersion')
            ->willReturn('9.3.4');

        $this->versionDataProvider
            ->expects($this->once())
            ->method('getPreviousVersion')
            ->willReturn('9.3.4');

        $this->stepsRunner
            ->expects($this->never())
            ->method('runPrepare');

        $this->stepsRunner
            ->expects($this->never())
            ->method('runAfterUpgrade');

        $this->configWriter
            ->expects($this->never())
            ->method('set');

        $runner = new Runner(
            $this->stepsProvider,
            $this->versionDataProvider,
            $this->stepsRunner,
            $this->dataManager,
            $this->configWriter
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $runner->run($this->io);
    }
}
