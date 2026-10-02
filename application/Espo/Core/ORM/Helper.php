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

namespace Espo\Core\ORM;

use Espo\Core\Utils\Config;
use Espo\ORM\Entity;

class Helper
{
    private const FORMAT_LAST_FIRST = 'lastFirst';
    private const FORMAT_LAST_FIRST_MIDDLE = 'lastFirstMiddle';
    private const FORMAT_FIRST_MIDDLE_LAST = 'firstMiddleLast';

    public function __construct(private Config $config)
    {}

    /**
     * @internal
     */
    public function hasAllPersonNameAttributes(Entity $entity, string $field): bool
    {
        $format = $this->config->get('personNameFormat');

        $firstName = 'first' . ucfirst($field);
        $lastName = 'last' . ucfirst($field);
        $middleName = 'middle' . ucfirst($field);

        $attributes = [
            $firstName,
            $lastName,
        ];

        if (
            $format === self::FORMAT_LAST_FIRST_MIDDLE ||
            $format === self::FORMAT_FIRST_MIDDLE_LAST
        ) {
            $attributes[] = $middleName;
        }

        foreach ($attributes as $attribute) {
            if (!$entity->has($attribute)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @internal
     */
    public function formatPersonName(Entity $entity, string $field): ?string
    {
        $format = $this->config->get('personNameFormat');

        $first = $entity->get('first' . ucfirst($field));
        $last = $entity->get('last' . ucfirst($field));
        $middle = $entity->get('middle' . ucfirst($field));

        switch ($format) {
            case self::FORMAT_LAST_FIRST:
                if ($first === null && $last === null) {
                    return null;
                }

                if ($first === null) {
                    return $last;
                }

                if ($last === null) {
                    return $first;
                }

                return $last . ' ' . $first;

            case self::FORMAT_LAST_FIRST_MIDDLE:
                if ($first === null && $last === null && $middle === null) {
                    return null;
                }

                $arr = [];

                if ($last !== null) {
                    $arr[] = $last;
                }

                if ($first !== null) {
                    $arr[] = $first;
                }

                if ($middle !== null) {
                    $arr[] = $middle;
                }

                return implode(' ', $arr);

            case self::FORMAT_FIRST_MIDDLE_LAST:
                if (!$first && !$last && !$middle) {
                    return null;
                }

                $arr = [];

                if ($first !== null) {
                    $arr[] = $first;
                }

                if ($middle !== null) {
                    $arr[] = $middle;
                }

                if ($last !== null) {
                    $arr[] = $last;
                }

                return implode(' ', $arr);
        }

        if ($first === null && $last === null) {
            return null;
        }

        if ($first === null) {
            return $last;
        }

        if ($last === null) {
            return $first;
        }

        return $first . ' ' . $last;
    }
}
