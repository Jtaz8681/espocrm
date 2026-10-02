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

namespace tests\integration\Espo\Core\Utils;

use Espo\Core\Utils\Json;
use Espo\Core\Utils\Metadata;
use tests\integration\Core\BaseTestCase;

class MetadataTest extends BaseTestCase
{
    private $filePath1 = 'custom/Espo/Custom/Resources/metadata/app/rebuild.json';
    private $filePath2 = 'custom/Espo/Custom/Resources/metadata/recordDefs/Note.json';

    protected function tearDown(): void
    {
        $this->getFileManager()->removeFile($this->filePath1);
        $this->getFileManager()->removeFile($this->filePath2);

        parent::tearDown();
    }

    public function testAppend1(): void
    {
        $initial = $this->getMetadata()->get(['app', 'rebuild', 'actionClassNameList']);

        $contents1 = Json::encode(
            (object) [
                'actionClassNameList' => [
                    "\\Espo\\Core\\Rebuild\\Actions\\ScheduledJobs",
                ]
            ]
        );

        $contents2 = Json::encode(
            (object) [
                'readLoaderClassNameList' => [
                    "\\Espo\\Classes\\FieldProcessing\\Note\\AdditionalFieldsLoader",
                ]
            ]
        );

        $this->createDirForFile($this->filePath1);
        $this->getFileManager()->putContents($this->filePath1, $contents1);
        $this->getFileManager()->putContents($this->filePath2, $contents2);
        $this->getDataManager()->clearCache();
        $this->getDataManager()->rebuildMetadata();

        $this->reCreateApplication(reuse: true);

        $metadata = $this->getContainer()->getByClass(Metadata::class);

        $this->assertSame(
            array_merge(
                $initial,
                ["\\Espo\\Core\\Rebuild\\Actions\\ScheduledJobs"]
            ),
            $metadata->get(['app', 'rebuild', 'actionClassNameList'])
        );

        $this->assertSame(
            [
                "Espo\\Classes\\FieldProcessing\\Note\\AdditionalFieldsLoader",
                "\\Espo\\Classes\\FieldProcessing\\Note\\AdditionalFieldsLoader",
            ],
            $metadata->get(['recordDefs', 'Note', 'readLoaderClassNameList'])
        );

        $this->getFileManager()->removeFile($this->filePath1);
        $this->getFileManager()->removeFile($this->filePath2);
        $this->getDataManager()->clearCache();
    }

    private function createDirForFile(string $filePath): void
    {
        $prevFolder = '';

        foreach (array_slice(explode('/', $filePath), 0, -1) as $folder) {
            $prevFolder .= $folder . '/';

            $this->getFileManager()->mkdir(substr($prevFolder, 0, -1));
        }
    }
}
