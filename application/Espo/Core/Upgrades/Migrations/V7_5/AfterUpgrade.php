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

namespace Espo\Core\Upgrades\Migrations\V7_5;

use Espo\Core\Templates\Entities\Event;
use Espo\Core\Upgrades\Migration\Script;
use Espo\Core\Utils\File\Manager;
use Espo\Core\Utils\Json;
use Espo\Core\Utils\Metadata;

class AfterUpgrade implements Script
{
    public function __construct(
        private Metadata $metadata,
        private Manager $fileManger
    ) {}

    public function run(): void
    {
        $this->updateEventMetadata();
    }

    private function updateEventMetadata(): void
    {
        $metadata = $this->metadata;
        $fileManager = $this->fileManger;

        $defs = $metadata->get(['scopes']);

        $path1 = "application/Espo/Core/Templates/Metadata/Event/selectDefs.json";
        $contents1 = $fileManager->getContents($path1);
        $data1 = Json::decode($contents1, true);

        $primaryFilterClassNameMap = (object) $data1['primaryFilterClassNameMap'];

        foreach ($defs as $entityType => $item) {
            $isCustom = $item['isCustom'] ?? false;
            $type = $item['type'] ?? false;

            if (!$isCustom || $type !== Event::TEMPLATE_TYPE) {
                continue;
            }

            $data1 = $metadata->getCustom('selectDefs', $entityType) ?? (object) [];
            $data1->primaryFilterClassNameMap = $primaryFilterClassNameMap;

            $metadata->saveCustom('selectDefs', $entityType, $data1);

            $data2 = $metadata->getCustom('scopes', $entityType) ?? (object) [];
            $data2->completedStatusList = ['Held'];
            $data2->canceledStatusList = ['Not Held'];

            $metadata->saveCustom('scopes', $entityType, $data2);
        }
    }
}
