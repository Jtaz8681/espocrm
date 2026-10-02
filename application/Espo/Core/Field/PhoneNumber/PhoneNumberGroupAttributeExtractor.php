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

namespace Espo\Core\Field\PhoneNumber;

use Espo\ORM\Value\AttributeExtractor;

use Espo\Core\Field\PhoneNumberGroup;

use stdClass;
use InvalidArgumentException;

/**
 * @implements AttributeExtractor<PhoneNumberGroup>
 */
class PhoneNumberGroupAttributeExtractor implements AttributeExtractor
{
    /**
     * @param PhoneNumberGroup $group
     */
    public function extract(object $group, string $field): stdClass
    {
        if (!$group instanceof PhoneNumberGroup) {
            throw new InvalidArgumentException();
        }

        $primaryNumber = $group->getPrimary() ? $group->getPrimary()->getNumber() : null;

        $dataList = [];

        foreach ($group->getList() as $phoneNumber) {
            $dataList[] = (object) [
                'phoneNumber' => $phoneNumber->getNumber(),
                'type' => $phoneNumber->getType(),
                'primary' => $primaryNumber && $phoneNumber->getNumber() === $primaryNumber,
                'optOut' => $phoneNumber->isOptedOut(),
                'invalid' => $phoneNumber->isInvalid(),
            ];
        }

        return (object) [
            $field => $primaryNumber,
            $field . 'Data' => $dataList,
        ];
    }

    public function extractFromNull(string $field): stdClass
    {
        return (object) [
            $field => null,
            $field . 'Data' => [],
        ];
    }
}
