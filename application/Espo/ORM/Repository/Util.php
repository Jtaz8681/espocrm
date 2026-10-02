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

namespace Espo\ORM\Repository;

use Espo\ORM\Entity;

use Espo\ORM\Type\RelationType;
use ReflectionClass;
use InvalidArgumentException;

class Util
{
    /**
     * @internal
     * @param class-string<Entity> $className
     */
    public static function getEntityTypeByClass(string $className): string
    {
        $class = new ReflectionClass($className);

        if (!$class->implementsInterface(Entity::class)) {
            throw new InvalidArgumentException();
        }

        if ($class->hasConstant('ENTITY_TYPE'))  {
            return (string) $class->getConstant('ENTITY_TYPE');
        }

        return $class->getShortName();
    }

    /**
     * @internal
     */
    public static function isRelationshipEligibleForCascadeRemoval(string $type, string $foreignType): bool
    {
        return in_array($type, [RelationType::HAS_CHILDREN, RelationType::HAS_ONE]) ||
            $type === RelationType::BELONGS_TO && $foreignType === RelationType::HAS_ONE ||
            $type === RelationType::HAS_MANY && $foreignType === RelationType::BELONGS_TO;
    }
}
