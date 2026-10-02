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

namespace Espo\Classes\FieldDuplicators\DashboardTemplate;

use Espo\Core\Record\Duplicator\FieldDuplicator;
use Espo\Core\Utils\ObjectUtil;
use Espo\Core\Utils\Util;
use Espo\Entities\DashboardTemplate;
use Espo\ORM\Entity;
use LogicException;
use RuntimeException;
use stdClass;

/**
 * @implements FieldDuplicator<DashboardTemplate>
 */
class Layout implements FieldDuplicator
{
    public function duplicate(Entity $entity, string $field): stdClass
    {
        $layout = $entity->getLayoutRaw();
        $options = $entity->getDashletsOptionsRaw();

        if (!$layout) {
            return (object) [];
        }

        $copyLayout = [];
        $idMap = [];

        foreach ($layout as $tab) {
            $copyLayout[] = $this->copyTab($tab, $idMap);
        }

        $copyOptions = (object) [];

        foreach (get_object_vars($options) as $id => $item) {
            $copyId = $idMap[$id] ?? null;

            if (!is_string($copyId)) {
                throw new LogicException();
            }

            if (!$item instanceof stdClass) {
                throw new RuntimeException("Bad dashboard options.");
            }

            $copyOptions->$copyId = ObjectUtil::clone($item);
        }

        return (object) [
            DashboardTemplate::FIELD_LAYOUT => $copyLayout,
            DashboardTemplate::FIELD_DASHLETS_OPTIONS => $copyOptions,
        ];
    }

    /**
     * @param array<string, string> $idMap
     */
    private function copyTab(stdClass $tab, array &$idMap): stdClass
    {
        $copy = ObjectUtil::clone($tab);

        $copy->id = Util::generateId();

        $layout = $copy->layout ?? [];

        foreach ($layout as $item) {
            if (!$item instanceof stdClass) {
                throw new RuntimeException("Bad layout dashlet definition.");
            }

            $id = $item->id ?? null;

            if (!is_string($id)) {
                throw new RuntimeException("No string ID in layout dashlet definition.");
            }

            $item->id = Util::generateId();

            $idMap[$id] = $item->id;
        }

        return $copy;
    }
}
