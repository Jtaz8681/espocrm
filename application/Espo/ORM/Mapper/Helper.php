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

namespace Espo\ORM\Mapper;

use Espo\ORM\Entity;
use Espo\ORM\Metadata;

use Espo\ORM\Name\Attribute;
use RuntimeException;

class Helper
{
    public function __construct(private Metadata $metadata)
    {}

    /**
     * @return array{
     *   key: string,
     *   foreignKey: string,
     *   foreignType?: string,
     *   nearKey?: string,
     *   distantKey?: string,
     *   typeKey?: string,
     * }
     */
    public function getRelationKeys(Entity $entity, string $relationName): array
    {
        $entityType = $entity->getEntityType();

        $defs = $this->metadata->getDefs()
            ->getEntity($entityType)
            ->getRelation($relationName);

        $type = $defs->getType();

        switch ($type) {

            case Entity::BELONGS_TO:
                $key = $defs->hasKey() ?
                    $defs->getKey() :
                    $relationName . 'Id';

                $foreignKey = $defs->hasForeignKey() ?
                    $defs->getForeignKey() :
                    Attribute::ID;

                return [
                    'key' => $key,
                    'foreignKey' => $foreignKey,
                ];

            case Entity::HAS_MANY:
            case Entity::HAS_ONE:
                $key = $defs->hasKey() ? $defs->getKey() : Attribute::ID;

                $foreign = $defs->hasForeignRelationName() ?
                    $defs->getForeignRelationName() :
                    null;

                $foreignKey = $defs->hasForeignKey() ?
                    $defs->getForeignKey() :
                    null;

                if (!$foreignKey && $foreign) {
                    $foreignKey = $foreign . 'Id';
                }

                if (!$foreignKey) {
                    $foreignKey = lcfirst($entity->getEntityType()) . 'Id';
                }

                return [
                    'key' => $key,
                    'foreignKey' => $foreignKey,
                ];

            case Entity::HAS_CHILDREN:
                $key = $defs->hasKey() ? $defs->getKey() : Attribute::ID;

                $foreignKey = $defs->hasForeignKey() ?
                    $defs->getForeignKey() :
                    'parentId';

                $foreignType = $defs->getParam('foreignType') ?? 'parentType';

                return [
                    'key' => $key,
                    'foreignKey' => $foreignKey,
                    'foreignType' => $foreignType,
                ];

            case Entity::MANY_MANY:
                $key = $defs->hasKey() ?
                    $defs->getKey() :
                    Attribute::ID;

                $foreignKey = $defs->hasForeignKey() ?
                    $defs->getForeignKey() :
                    Attribute::ID;

                $nearKey = $defs->hasMidKey() ?
                    $defs->getMidKey() :
                    lcfirst($entityType) . 'Id';

                $distantKey = $defs->hasForeignMidKey() ?
                    $defs->getForeignMidKey() :
                    lcfirst($defs->getForeignEntityType()) . 'Id';

                return [
                    'key' => $key,
                    'foreignKey' => $foreignKey,
                    'nearKey' => $nearKey,
                    'distantKey' => $distantKey,
                ];

            case Entity::BELONGS_TO_PARENT:
                $key = $relationName . 'Id';
                $typeKey = $relationName . 'Type';

                return [
                    'key' => $key,
                    'typeKey' => $typeKey,
                    'foreignKey' => Attribute::ID,
                ];
        }

        throw new RuntimeException("Relation type '{$type}' not supported for 'getKeys'.");
    }
}
