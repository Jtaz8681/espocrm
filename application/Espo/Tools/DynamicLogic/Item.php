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

namespace Espo\Tools\DynamicLogic;

use Espo\Tools\DynamicLogic\Exceptions\BadCondition;
use stdClass;

readonly class Item
{
    public function __construct(
        public Type $type,
        public mixed $value,
        public ?string $attribute = null,
    ) {}

    /**
     * @param stdClass[] $rawItems
     * @throws BadCondition
     */
    public static function fromGroupDefinition(array $rawItems): Item
    {
        return new Item(
            type: Type::And,
            value: array_map(fn ($it) => self::fromItemDefinition($it), $rawItems),
        );
    }

    /**
     * @throws BadCondition
     */
    public static function fromItemDefinition(stdClass $rawItem): Item
    {
        $type = $rawItem->type ?? null;
        $attribute = $rawItem->attribute ?? null;
        $value = $rawItem->value ?? null;

        if (!$type || !is_string($type)) {
            throw new BadCondition("No type.");
        }

        if ($type === 'has') {
            $type = 'contains';
        }

        if ($type === Type::And->value || $type === Type::Or->value) {
            if (!is_array($value)) {
                throw new BadCondition("Non-array value.");
            }

            foreach ($value as $it) {
                if (!$it instanceof stdClass) {
                    throw new BadCondition("Bad group item value.");
                }
            }

            return new Item(
                type: Type::from($type),
                value: array_map(fn ($it) => self::fromItemDefinition($it), $value),
            );
        }

        if ($type === Type::Not->value) {
            if (!$value instanceof stdClass) {
                throw new BadCondition("Bad not item value.");
            }

            return new Item(
                type: Type::from($type),
                value: self::fromItemDefinition($value),
            );
        }

        if ($attribute !== null && !is_string($attribute)) {
            throw new BadCondition("No attribute.");
        }

        return new Item(
            type: Type::from($type),
            value: $value,
            attribute: $attribute,
        );
    }
}
