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

namespace Espo\Core\Field\EmailAddress;

use Espo\ORM\Value\AttributeExtractor;

use Espo\Core\Field\EmailAddressGroup;

use stdClass;
use InvalidArgumentException;

/**
 * @implements AttributeExtractor<EmailAddressGroup>
 */
class EmailAddressGroupAttributeExtractor implements AttributeExtractor
{
    /**
     * @param EmailAddressGroup $group
     */
    public function extract(object $group, string $field): stdClass
    {
        if (!$group instanceof EmailAddressGroup) {
            throw new InvalidArgumentException();
        }

        $primaryAddress = $group->getPrimary() ? $group->getPrimary()->getAddress() : null;

        $dataList = [];

        foreach ($group->getList() as $emailAddress) {
            $dataList[] = (object) [
                'emailAddress' => $emailAddress->getAddress(),
                'lower' => strtolower($emailAddress->getAddress()),
                'primary' => $primaryAddress && $emailAddress->getAddress() === $primaryAddress,
                'optOut' => $emailAddress->isOptedOut(),
                'invalid' => $emailAddress->isInvalid(),
            ];
        }

        return (object) [
            $field => $primaryAddress,
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
