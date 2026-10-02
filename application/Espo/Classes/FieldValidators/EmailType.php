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

namespace Espo\Classes\FieldValidators;

use Espo\Core\Name\Field;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\ORM\Entity;

use stdClass;

class EmailType
{
    private const DEFAULT_MAX_LENGTH = 255;
    private const MAX_COUNT = 10;

    public function __construct(
        private Metadata $metadata,
        private Config $config,
    ) {}

    public function checkRequired(Entity $entity, string $field): bool
    {
        if ($this->isNotEmpty($entity, $field)) {
            return true;
        }

        $dataList = $entity->get($field . 'Data');

        if (!is_array($dataList)) {
            return false;
        }

        foreach ($dataList as $item) {
            if (!empty($item->emailAddress)) {
                return true;
            }
        }

        return false;
    }

    public function checkEmailAddress(Entity $entity, string $field): bool
    {
        if ($this->isNotEmpty($entity, $field)) {
            $address = $entity->get($field);

            if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }

        $dataList = $entity->get($field . 'Data');

        if (!is_array($dataList)) {
            return true;
        }

        foreach ($dataList as $item) {
            if (!$item instanceof stdClass) {
                return false;
            }

            if (empty($item->emailAddress)) {
                continue;
            }

            $address = $item->emailAddress;

            if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }

        return true;
    }

    public function checkMaxLength(Entity $entity, string $field): bool
    {
        /** @var ?string $value */
        $value = $entity->get($field);

        /** @var int $maxLength */
        $maxLength = $this->metadata
                ->get(['entityDefs', 'EmailAddress', 'fields', Field::NAME, FieldParam::MAX_LENGTH]) ??
            self::DEFAULT_MAX_LENGTH;

        if ($value && mb_strlen($value) > $maxLength) {
            return false;
        }

        $dataList = $entity->get($field . 'Data');

        if (!is_array($dataList)) {
            return true;
        }

        foreach ($dataList as $item) {
            $value = $item->emailAddress;

            if ($value && mb_strlen($value) > $maxLength) {
                return false;
            }
        }

        return true;
    }

    public function checkMaxCount(Entity $entity, string $field): bool
    {
        $maxCount = $this->config->get('emailAddressMaxCount') ?? self::MAX_COUNT;

        $dataList = $entity->get($field . 'Data');

        if (!is_array($dataList)) {
            return true;
        }

        return count($dataList) <= $maxCount;
    }

    protected function isNotEmpty(Entity $entity, string $field): bool
    {
        return $entity->has($field) && $entity->get($field) !== '' && $entity->get($field) !== null;
    }
}
