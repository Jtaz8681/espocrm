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

use Espo\Core\Utils\Util;
use Espo\ORM\Defs\Params\FieldParam;

/**
 * @internal
 */
class BuilderHelper
{
    /**
     * A list of copy-from-parent params for metadata -> fields.
     *
     * @var string[]
     */
    private array $copiedDefParams = [
        'readOnly',
        'disabled',
        FieldParam::NOT_STORABLE,
        'layoutListDisabled',
        'layoutDetailDisabled',
        'layoutMassUpdateDisabled',
        'layoutFiltersDisabled',
        'directAccessDisabled',
        'directUpdateDisabled',
        'customizationDisabled',
        'importDisabled',
        'exportDisabled',
    ];

    private string $defaultFieldNaming = 'postfix';

    /**
     * Get additional field list based on field definition in metadata 'fields'.
     *
     * @param string $field
     * @param array<string, mixed> $params
     * @param array<string, mixed> $defs
     * @return ?array<string, mixed>
     * @internal
     */
    public function getAdditionalFields(string $field, array $params, array $defs): ?array
    {
        if (!$defs) {
            return null;
        }

        $type = $params['type'] ?? null;

        if (!$type) {
            return null;
        }

        $typeDefs = $defs[$type] ?? null;

        if (!$typeDefs) {
            return null;
        }

        /** @var ?array<string, mixed> $fields */
        $fields = $typeDefs['fields'] ?? null;
        /** @var string $naming */
        $naming = $typeDefs['naming'] ?? $this->defaultFieldNaming;

        if (!is_array($fields)) {
            return null;
        }

        $copiedParams = array_intersect_key($params, array_flip($this->copiedDefParams));

        $output = [];

        foreach ($fields as $subField => $subParams) {
            $subName = Util::getNaming($field, $subField, $naming);

            $output[$subName] = array_merge($copiedParams, $subParams);

            // A trick to allow some fields to be combined with the main field.
            if (array_key_exists('detailLayoutIncompatibleFieldList', $output[$subName])) {
                continue;
            }

            $output[$subName]['detailLayoutIncompatibleFieldList'] = [$field];
        }

        return $output;
    }
}
