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

namespace Espo\Core\Field\LinkParent;

use Espo\ORM\Value\AttributeExtractor;

use Espo\Core\Field\LinkParent;

use stdClass;
use InvalidArgumentException;

/**
 * @implements AttributeExtractor<LinkParent>
 */
class LinkParentAttributeExtractor implements AttributeExtractor
{
    /**
     * @param LinkParent $value
     */
    public function extract(object $value, string $field): stdClass
    {
        if (!$value instanceof LinkParent) {
            throw new InvalidArgumentException();
        }

        return (object) [
            $field . 'Id' => $value->getId(),
            $field . 'Type' => $value->getEntityType(),
            $field . 'Name' => $value->getName(),
        ];
    }

    public function extractFromNull(string $field): stdClass
    {
        return (object) [
            $field . 'Id' => null,
            $field . 'Type' => null,
            $field . 'Name' => null,
        ];
    }
}
