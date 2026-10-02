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

namespace Espo\Core\Utils\Metadata\AdditionalBuilder;

use Espo\Core\Utils\DataUtil;
use Espo\Core\Utils\Metadata\AdditionalBuilder;
use Espo\Core\Utils\Metadata\BuilderHelper;
use Espo\Core\Utils\Util;
use RuntimeException;
use stdClass;

class Fields implements AdditionalBuilder
{
    private BuilderHelper $builderHelper;

    public function __construct()
    {
        $this->builderHelper = new BuilderHelper();
    }

    public function build(stdClass $data): void
    {
        if (!isset($data->entityDefs)) {
            return;
        }

        $defs = Util::objectToArray($data->fields);

        foreach (get_object_vars($data->entityDefs) as $entityType => $entityDefsItem) {
            if (isset($data->entityDefs->$entityType->collection)) {
                /** @var stdClass $collectionItem */
                $collectionItem = $data->entityDefs->$entityType->collection;

                if (isset($collectionItem->orderBy)) {
                    $collectionItem->sortBy = $collectionItem->orderBy;
                } else if (isset($collectionItem->sortBy)) {
                    $collectionItem->orderBy = $collectionItem->sortBy;
                }

                if (isset($collectionItem->order)) {
                    $collectionItem->asc = $collectionItem->order === 'asc';
                } else if (isset($collectionItem->asc)) {
                    $collectionItem->order = $collectionItem->asc === true ? 'asc' : 'desc';
                }
            }

            if (!isset($entityDefsItem->fields)) {
                continue;
            }

            foreach (get_object_vars($entityDefsItem->fields) as $field => $fieldDefsItem) {
                if (!is_object($fieldDefsItem)) {
                    throw new RuntimeException("Bad definition for $entityType.$field field.");
                }

                $additionalFields = $this->builderHelper->getAdditionalFields(
                    field: $field,
                    params: Util::objectToArray($fieldDefsItem),
                    defs: $defs,
                );

                if (!$additionalFields) {
                    continue;
                }

                foreach ($additionalFields as $subFieldName => $subFieldParams) {
                    $item = Util::arrayToObject($subFieldParams);

                    if (isset($entityDefsItem->fields->$subFieldName)) {
                        $data->entityDefs->$entityType->fields->$subFieldName =
                            DataUtil::merge(
                                $item,
                                $entityDefsItem->fields->$subFieldName
                            );

                        continue;
                    }

                    $data->entityDefs->$entityType->fields->$subFieldName = $item;
                }
            }
        }
    }
}
