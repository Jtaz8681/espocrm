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

namespace Espo\Tools\OpenApi\Util;

use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs\FieldDefs;

class EnumOptionsProvider
{
    public function __construct(
        private Metadata $metadata,
    ) {}

    /**
     * @return ?string[]
     */
    public function get(FieldDefs $fieldDefs): ?array
    {
        /** @var ?string $path */
        $path = $fieldDefs->getParam('optionsPath');
        /** @var ?string $ref */
        $ref = $fieldDefs->getParam('optionsReference');

        if (!$path && $ref && str_contains($ref, '.')) {
            [$refEntityType, $refField] = explode('.', $ref);

            $path = "entityDefs.$refEntityType.fields.$refField.options";
        }

        /** @var ?string[] $optionList */
        $optionList = $path ?
            $this->metadata->get($path) :
            $fieldDefs->getParam('options');

        return $optionList;
    }
}
