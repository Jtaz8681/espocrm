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

namespace Espo\Core\Field\LinkMultiple;

use Espo\ORM\Value\AttributeExtractor;

use Espo\Core\Field\LinkMultiple;

use stdClass;
use InvalidArgumentException;

/**
 * @implements AttributeExtractor<LinkMultiple>
 */
class LinkMultipleAttributeExtractor implements AttributeExtractor
{
    /**
     * @param LinkMultiple $value
     */
    public function extract(object $value, string $field): stdClass
    {
        if (!$value instanceof LinkMultiple) {
            throw new InvalidArgumentException();
        }

        $nameMap = (object) [];
        $columnData = (object) [];

        foreach ($value->getList() as $item) {
            $id = $item->getId();

            $nameMap->$id = $item->getName();

            $columnItemData = (object) [];

            foreach ($item->getColumnList() as $column) {
                $columnItemData->$column = $item->getColumnValue($column);
            }

            $columnData->$id = $columnItemData;
        }

        return (object) [
            $field . 'Ids' => $value->getIdList(),
            $field . 'Names' => $nameMap,
            $field . 'Columns' => $columnData,
        ];
    }

    public function extractFromNull(string $field): stdClass
    {
        return (object) [
            $field . 'Ids' => [],
            $field . 'Names' => (object) [],
            $field . 'Columns' => (object) [],
        ];
    }
}
