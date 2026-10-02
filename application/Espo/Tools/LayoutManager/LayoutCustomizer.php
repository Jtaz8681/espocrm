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

namespace Espo\Tools\LayoutManager;

use Espo\Core\Exceptions\Error;
use Espo\Core\Utils\Json;
use Espo\Tools\Layout\LayoutProvider;
use RuntimeException;
use stdClass;

/**
 * @since 10.0.0
 */
class LayoutCustomizer
{
    public function __construct(
        private LayoutProvider $layoutProvider,
        private LayoutManager $layoutManager,
    ) {}

    /**
     * @throws Error
     */
    public function addDetailField(string $entityType, string $field, string $layoutName): void
    {
        $layoutData = $this->getDetailLayout($entityType, $layoutName);

        if ($this->hasInDetail($layoutData, $field)) {
            return;
        }

        $lastPanel = $layoutData[count($layoutData) - 1];

        if (!$lastPanel instanceof stdClass) {
            throw new RuntimeException("Bad layout panel definition in $entityType.$layoutName.");
        }

        if (isset($lastPanel->cols) && is_array($lastPanel->cols)) {
            $cols = $lastPanel->cols;

            $cols[] = [[(object) ['name' => $field]]];

            $lastPanel->cols = $cols;
        } else {
            $rows = $lastPanel->rows ?? [];

            $rows[] = [(object) ['name' => $field], false];

            $lastPanel->rows = $rows;
        }

        $this->layoutManager->set($layoutData, $entityType, $layoutName);
        $this->layoutManager->save();
    }

    /**
     * @throws Error
     */
    public function removeInDetail(string $entityType, string $field, string $layoutName): void
    {
        $panels = $this->getDetailLayout($entityType, $layoutName);

        $cell = $this->getCellFromDetail($panels, $field);

        if (!$cell) {
            return;
        }

        foreach ($panels as $panelItem) {
            if (isset($panelItem->cols)) {
                $rowItems =& $panelItem->cols;
            } else if (isset($panelItem->rows)) {
                $rowItems =& $panelItem->rows;
            } else {
                continue;
            }

            if (!is_array($rowItems)) {
                continue;
            }

            foreach ($rowItems as $rowIndex => &$rowItem) {
                if (!is_array($rowItem)) {
                    continue;
                }

                $found = false;

                foreach ($rowItem as $i => $cellItem) {
                    if (!$cellItem instanceof stdClass) {
                        continue;
                    }

                    if (($cellItem->name ?? null) === $field) {
                        $rowItem[$i] = false;

                        $found = true;
                    }
                }

                if (!$found) {
                    continue;
                }

                $notEmptyRow = false;

                foreach ($rowItem as $cellItem) {
                    if ($cellItem !== false) {
                        $notEmptyRow = true;

                        break;
                    }
                }

                if (!$notEmptyRow) {
                    unset($rowItems[$rowIndex]);

                    $rowItems = array_values($rowItems);
                }
            }
        }

        $this->layoutManager->set($panels, $entityType, $layoutName);
        $this->layoutManager->save();
    }

    /**
     * @param array<int, mixed> $panels
     */
    private function hasInDetail(array $panels, string $field): bool
    {
        return $this->getCellFromDetail($panels, $field) !== null;
    }

    /**
     * @param array<int, mixed> $panels
     */
    private function getCellFromDetail(array $panels, string $field): ?stdClass
    {
        foreach ($panels as $panelItem) {
            $rowItems = $panelItem->cols ?? $panelItem->rows ?? null;

            if (!is_array($rowItems)) {
                continue;
            }

            foreach ($rowItems as $rowItem) {
                if (!is_array($rowItem)) {
                    continue;
                }

                foreach ($rowItem as $cellItem) {
                    if (!$cellItem instanceof stdClass) {
                        continue;
                    }

                    if (($cellItem->name ?? null) === $field) {
                        return $cellItem;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, mixed>
     */
    private function getDetailLayout(string $entityType, string $layoutName): array
    {
        $layoutString = $this->layoutProvider->get($entityType, $layoutName);

        if (!$layoutString) {
            $layoutString = '[]';
        }

        $layoutData = Json::decode($layoutString);

        if (!is_array($layoutData)) {
            throw new RuntimeException("Bad layout $entityType.$layoutName.");
        }

        return $layoutData;
    }
}
