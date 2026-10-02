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

namespace Espo\Classes\FieldConverters;

use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Orm\FieldConverter;
use Espo\ORM\Defs\FieldDefs;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Type\AttributeType;
use RuntimeException;

class RelationshipRole implements FieldConverter
{
    public function convert(FieldDefs $fieldDefs, string $entityType): EntityDefs
    {
        $name = $fieldDefs->getName();

        $attributeDefs = AttributeDefs::create($name)
            ->withType(AttributeType::VARCHAR)
            ->withNotStorable();

        $attributeDefs = $this->addWhere($attributeDefs, $fieldDefs, $entityType);

        return EntityDefs::create()
            ->withAttribute($attributeDefs);
    }

    private function addWhere(AttributeDefs $attributeDefs, FieldDefs $fieldDefs, string $entityType): AttributeDefs
    {
        $data = $fieldDefs->getParam('converterData');

        if (!is_array($data)) {
            throw new RuntimeException("No `converterData` in field defs.");
        }

        /** @var ?string $column */
        $column = $data['column'] ?? null;
        /** @var ?string $link */
        $link = $data['link'] ?? null;
        /** @var ?string $relationName */
        $relationName = $data[RelationParam::RELATION_NAME] ?? null;
        /** @var ?string $nearKey */
        $nearKey = $data['nearKey'] ?? null;

        if (!$column || !$link || !$relationName || !$nearKey) {
            throw new RuntimeException("Bad `converterData`.");
        }

        $midTable = ucfirst($relationName);

        return $attributeDefs->withParamsMerged([
            'where' => [
                '=' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => $midTable,
                            'select' => [$nearKey],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                $column => '{value}',
                            ],
                        ],
                    ],
                ],
                '<>' => [
                    'whereClause' => [
                        'id!=s' => [
                            'from' => $midTable,
                            'select' => [$nearKey],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                $column => '{value}',
                            ],
                        ],
                    ],
                ],
                'IN' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => $midTable,
                            'select' => [$nearKey],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                $column => '{value}',
                            ],
                        ],
                    ],
                ],
                'NOT IN' => [
                    'whereClause' => [
                        'id!=s' => [
                            'from' => $midTable,
                            'select' => [$nearKey],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                $column => '{value}',
                            ],
                        ],
                    ],
                ],
                'LIKE' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => $midTable,
                            'select' => [$nearKey],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                "$column*" => '{value}',
                            ],
                        ],
                    ],
                ],
                'NOT LIKE' => [
                    'whereClause' => [
                        'id!=s' => [
                            'from' => $midTable,
                            'select' => [$nearKey],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                "$column*" => '{value}',
                            ],
                        ],
                    ],
                ],
                'IS NULL' => [
                    'whereClause' => [
                        'NOT' => [
                            'EXISTS' => [
                                'from' => $entityType,
                                'fromAlias' => 'sq',
                                'select' => [Attribute::ID],
                                'leftJoins' => [
                                    [
                                        $link,
                                        'm',
                                        null,
                                        ['onlyMiddle' => true]
                                    ]
                                ],
                                'whereClause' => [
                                    "m.$column!=" => null,
                                    'sq.id:' => lcfirst($entityType) . '.id',
                                ],
                            ],
                        ],
                    ],
                ],
                'IS NOT NULL' => [
                    'whereClause' => [
                        'EXISTS' => [
                            'from' => $entityType,
                            'fromAlias' => 'sq',
                            'select' => [Attribute::ID],
                            'leftJoins' => [
                                [
                                    $link,
                                    'm',
                                    null,
                                    ['onlyMiddle' => true]
                                ]
                            ],
                            'whereClause' => [
                                "m.$column!=" => null,
                                'sq.id:' => lcfirst($entityType) . '.id',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
