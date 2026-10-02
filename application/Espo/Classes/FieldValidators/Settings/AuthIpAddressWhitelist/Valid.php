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

namespace Espo\Classes\FieldValidators\Settings\AuthIpAddressWhitelist;

use Espo\Core\FieldValidation\Validator;
use Espo\Core\FieldValidation\Validator\Data;
use Espo\Core\FieldValidation\Validator\Failure;
use Espo\ORM\Entity;

/**
 * @implements Validator<Entity>
 */
class Valid implements Validator
{
    public function validate(Entity $entity, string $field, Data $data): ?Failure
    {
        $list = $entity->get($field);

        if (!is_array($list)) {
            return null;
        }

        foreach ($list as $item) {
            if (!is_string($item)) {
                continue;
            }

            if (!$this->isValid($item)) {
                return Failure::create();
            }
        }

        return null;
    }

    private function isValid(string $item): bool
    {
        $address = $item;

        if (count(explode('/', $item)) > 1) {
            [$address, $mask] = explode('/', $item, 2);

            if (!is_numeric($mask)) {
                return false;
            }

            $mask = (int) $mask;

            if ($mask < 0 || $mask > 128) {
                return false;
            }
        }

        if (
            filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false &&
            filter_var($address, FILTER_VALIDATE_IP) === false
        ) {
            return false;
        }

        return true;
    }
}
