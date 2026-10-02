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

namespace Espo\Core\Utils\Metadata;

use Espo\Core\Utils\Metadata;

class Helper
{
    public function __construct(private Metadata $metadata)
    {}

    /**
     * Get field definitions by a type in metadata, "fields" key.
     *
     * @param array<string, mixed> $defs It can be a string or field definition from entityDefs.
     * @return ?array<string, mixed>
     */
    public function getFieldDefsByType($defs)
    {
        if (isset($defs['type'])) {
            return $this->metadata->get('fields.' . $defs['type']);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $defs
     * @return ?array<string, mixed>
     */
    public function getFieldDefsInFieldMetadata($defs)
    {
        $fieldDefsByType = $this->getFieldDefsByType($defs);

        if (isset($fieldDefsByType['fieldDefs'])) {
            return $fieldDefsByType['fieldDefs'];
        }

        return null;
    }

    /**
     * Get link definition defined in 'fields' metadata.
     * In linkDefs can be used as value (e.g. "type": "hasChildren") and/or variables (e.g. "entityName": "{entity}").
     * Variables should be defined into fieldDefs (in 'entityDefs' metadata).
     *
     * @param string $entityType
     * @param array<string, mixed> $defs
     * @return ?array<string, mixed>
     */
    public function getLinkDefsInFieldMeta($entityType, $defs)
    {
        $fieldDefsByType = $this->getFieldDefsByType($defs);

        if (!isset($fieldDefsByType['linkDefs'])) {
            return null;
        }

        $linkFieldDefsByType = $fieldDefsByType['linkDefs'];

        foreach ($linkFieldDefsByType as &$paramValue) {
            if (preg_match('/{(.*?)}/', $paramValue, $matches)) {
                if (in_array($matches[1], array_keys($defs))) {
                    $value = $defs[$matches[1]];
                } else if (strtolower($matches[1]) == 'entity') {
                    $value = $entityType;
                }

                if (isset($value)) {
                    $paramValue = str_replace('{'.$matches[1].'}', $value, $paramValue);
                }
            }
        }

        return $linkFieldDefsByType;
    }
}
