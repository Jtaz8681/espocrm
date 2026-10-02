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

namespace Espo\Core\Utils\Database\Orm\FieldConverters;

use Espo\Core\ORM\Defs\AttributeParam;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Orm\Defs\RelationDefs;
use Espo\Core\Utils\Database\Orm\FieldConverter;
use Espo\Entities\EmailAddress;
use Espo\ORM\Defs\FieldDefs;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;

class Email implements FieldConverter
{
    private const COLUMN_ENTITY_TYPE_LENGTH = 100;

    public function convert(FieldDefs $fieldDefs, string $entityType): EntityDefs
    {
        $name = $fieldDefs->getName();

        $foreignJoinAlias = "$name$entityType{alias}Foreign";
        $foreignJoinMiddleAlias = "$name$entityType{alias}ForeignMiddle";

        $emailAddressDefs = AttributeDefs
            ::create($name)
            ->withType(AttributeType::VARCHAR)
            ->withParamsMerged(
                $this->getEmailAddressParams($entityType, $foreignJoinAlias, $foreignJoinMiddleAlias)
            );

        $dataDefs = AttributeDefs
            ::create($name . 'Data')
            ->withType(AttributeType::JSON_ARRAY)
            ->withNotStorable()
            ->withParamsMerged([
                AttributeParam::NOT_EXPORTABLE => true,
                'isEmailAddressData' => true,
                'field' => $name,
            ]);

        $isOptedOutDefs = AttributeDefs
            ::create($name . 'IsOptedOut')
            ->withType(AttributeType::BOOL)
            ->withNotStorable()
            ->withParamsMerged(
                $this->getIsOptedOutParams($foreignJoinAlias, $foreignJoinMiddleAlias)
            );

        $isInvalidDefs = AttributeDefs
            ::create($name . 'IsInvalid')
            ->withType(AttributeType::BOOL)
            ->withNotStorable()
            ->withParamsMerged(
                $this->getIsInvalidParams($foreignJoinAlias, $foreignJoinMiddleAlias)
            );

        $relationDefs = RelationDefs
            ::create('emailAddresses')
            ->withType(RelationType::MANY_MANY)
            ->withForeignEntityType(EmailAddress::ENTITY_TYPE)
            ->withRelationshipName('entityEmailAddress')
            ->withMidKeys('entityId', 'emailAddressId')
            ->withConditions(['entityType' => $entityType])
            ->withAdditionalColumn(
                AttributeDefs
                    ::create('entityType')
                    ->withType(AttributeType::VARCHAR)
                    ->withLength(self::COLUMN_ENTITY_TYPE_LENGTH)
            )
            ->withAdditionalColumn(
                AttributeDefs
                    ::create('primary')
                    ->withType(AttributeType::BOOL)
                    ->withDefault(false)
            );

        return EntityDefs::create()
            ->withAttribute($emailAddressDefs)
            ->withAttribute($dataDefs)
            ->withAttribute($isOptedOutDefs)
            ->withAttribute($isInvalidDefs)
            ->withRelation($relationDefs);
    }

    /**
     * @return array<string, mixed>
     */
    private function getEmailAddressParams(
        string $entityType,
        string $foreignJoinAlias,
        string $foreignJoinMiddleAlias,
    ): array {

        return [
            'select' => [
                "select" => "emailAddresses.name",
                'leftJoins' => [['emailAddresses', 'emailAddresses', ['primary' => true]]],
            ],
            'selectForeign' => [
                "select" => "$foreignJoinAlias.name",
                'leftJoins' => [
                    [
                        'EntityEmailAddress',
                        $foreignJoinMiddleAlias,
                        [
                            "$foreignJoinMiddleAlias.entityId:" => "{alias}.id",
                            "$foreignJoinMiddleAlias.primary" => true,
                            "$foreignJoinMiddleAlias.deleted" => false,
                        ]
                    ],
                    [
                        EmailAddress::ENTITY_TYPE,
                        $foreignJoinAlias,
                        [
                            "$foreignJoinAlias.id:" => "$foreignJoinMiddleAlias.emailAddressId",
                            "$foreignJoinAlias.deleted" => false,
                        ]
                    ]
                ],
            ],
            'fieldType' => FieldType::EMAIL,
            'where' => [
                'LIKE' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => 'EntityEmailAddress',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'emailAddress',
                                    'emailAddress',
                                    [
                                        'emailAddress.id:' => 'emailAddressId',
                                        'emailAddress.deleted' => false,
                                    ],
                                ]
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                "LIKE:(emailAddress.lower, LOWER:({value})):" => null,
                            ],
                        ],
                    ],
                ],
                'NOT LIKE' => [
                    'whereClause' => [
                        'id!=s' => [
                            'from' => 'EntityEmailAddress',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'emailAddress',
                                    'emailAddress',
                                    [
                                        'emailAddress.id:' => 'emailAddressId',
                                        'emailAddress.deleted' => false,
                                    ],
                                ]
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                "LIKE:(emailAddress.lower, LOWER:({value})):" => null,
                            ],
                        ],
                    ],
                ],
                '=' => [
                    'leftJoins' => [['emailAddresses', 'emailAddressesMultiple']],
                    'whereClause' => [
                        "EQUAL:(emailAddressesMultiple.lower, LOWER:({value})):" => null,
                    ]
                ],
                '<>' => [
                    'whereClause' => [
                        'id!=s' => [
                            'from' => 'EntityEmailAddress',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'emailAddress',
                                    'emailAddress',
                                    [
                                        'emailAddress.id:' => 'emailAddressId',
                                        'emailAddress.deleted' => false,
                                    ],
                                ]
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                "EQUAL:(emailAddress.lower, LOWER:({value})):" => null,
                            ],
                        ],
                    ],
                ],
                'IN' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => 'EntityEmailAddress',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'emailAddress',
                                    'emailAddress',
                                    [
                                        'emailAddress.id:' => 'emailAddressId',
                                        'emailAddress.deleted' => false,
                                    ],
                                ]
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                "emailAddress.lower" => '{value}',
                            ],
                        ],
                    ],
                ],
                'NOT IN' => [
                    'whereClause' => [
                        'id!=s' => [
                            'from' => 'EntityEmailAddress',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'emailAddress',
                                    'emailAddress',
                                    [
                                        'emailAddress.id:' => 'emailAddressId',
                                        'emailAddress.deleted' => false,
                                    ],
                                ]
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                "emailAddress.lower" => '{value}',
                            ],
                        ],
                    ],
                ],
                'IS NULL' => [
                    'leftJoins' => [['emailAddresses', 'emailAddressesMultiple']],
                    'whereClause' => [
                        'emailAddressesMultiple.lower=' => null,
                    ]
                ],
                'IS NOT NULL' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => 'EntityEmailAddress',
                            'select' => ['entityId'],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                            ],
                        ],
                    ],
                ],
            ],
            'order' => [
                'order' => [
                    ['emailAddresses.lower', '{direction}'],
                ],
                'leftJoins' => [['emailAddresses', 'emailAddresses', ['primary' => true]]],
                'additionalSelect' => ['emailAddresses.lower'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getIsOptedOutParams(string $foreignJoinAlias, string $foreignJoinMiddleAlias): array
    {
        return [
            'select' => [
                'select' => "emailAddresses.optOut",
                'leftJoins' => [['emailAddresses', 'emailAddresses', ['primary' => true]]],
            ],
            'selectForeign' => [
                'select' => "$foreignJoinAlias.optOut",
                'leftJoins' => [
                    [
                        'EntityEmailAddress',
                        $foreignJoinMiddleAlias,
                        [
                            "$foreignJoinMiddleAlias.entityId:" => "{alias}.id",
                            "$foreignJoinMiddleAlias.primary" => true,
                            "$foreignJoinMiddleAlias.deleted" => false,
                        ]
                    ],
                    [
                        EmailAddress::ENTITY_TYPE,
                        $foreignJoinAlias,
                        [
                            "$foreignJoinAlias.id:" => "$foreignJoinMiddleAlias.emailAddressId",
                            "$foreignJoinAlias.deleted" => false,
                        ]
                    ]
                ],
            ],
            'where' => [
                '= TRUE' => [
                    'whereClause' => [
                        ['emailAddresses.optOut=' => true],
                        ['emailAddresses.optOut!=' => null],
                    ],
                    'leftJoins' => [['emailAddresses', 'emailAddresses', ['primary' => true]]],
                ],
                '= FALSE' => [
                    'whereClause' => [
                        'OR' => [
                            ['emailAddresses.optOut=' => false],
                            ['emailAddresses.optOut=' => null],
                        ]
                    ],
                    'leftJoins' => [['emailAddresses', 'emailAddresses', ['primary' => true]]],
                ]
            ],
            'order' => [
                'order' => [
                    ['emailAddresses.optOut', '{direction}'],
                ],
                'leftJoins' => [['emailAddresses', 'emailAddresses', ['primary' => true]]],
                'additionalSelect' => ['emailAddresses.optOut'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getIsInvalidParams(string $foreignJoinAlias, string $foreignJoinMiddleAlias): array
    {
        return [
            'select' => [
                'select' => "emailAddresses.invalid",
                'leftJoins' => [['emailAddresses', 'emailAddresses', ['primary' => true]]],
            ],
            'selectForeign' => [
                'select' => "$foreignJoinAlias.invalid",
                'leftJoins' => [
                    [
                        'EntityEmailAddress',
                        $foreignJoinMiddleAlias,
                        [
                            "$foreignJoinMiddleAlias.entityId:" => "{alias}.id",
                            "$foreignJoinMiddleAlias.primary" => true,
                            "$foreignJoinMiddleAlias.deleted" => false,
                        ]
                    ],
                    [
                        EmailAddress::ENTITY_TYPE,
                        $foreignJoinAlias,
                        [
                            "$foreignJoinAlias.id:" => "$foreignJoinMiddleAlias.emailAddressId",
                            "$foreignJoinAlias.deleted" => false,
                        ]
                    ]
                ],
            ],
            'where' => [
                '= TRUE' => [
                    'whereClause' => [
                        ['emailAddresses.invalid=' => true],
                        ['emailAddresses.invalid!=' => null],
                    ],
                    'leftJoins' => [['emailAddresses', 'emailAddresses', ['primary' => true]]],
                ],
                '= FALSE' => [
                    'whereClause' => [
                        'OR' => [
                            ['emailAddresses.invalid=' => false],
                            ['emailAddresses.invalid=' => null],
                        ]
                    ],
                    'leftJoins' => [['emailAddresses', 'emailAddresses', ['primary' => true]]],
                ]
            ],
            'order' => [
                'order' => [
                    ['emailAddresses.invalid', '{direction}'],
                ],
                'leftJoins' => [['emailAddresses', 'emailAddresses', ['primary' => true]]],
                'additionalSelect' => ['emailAddresses.invalid'],
            ],
        ];
    }
}
