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
use Espo\Entities\PhoneNumber;
use Espo\ORM\Defs\FieldDefs;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;

/**
 * @noinspection PhpUnused
 */
class Phone implements FieldConverter
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
                $this->getPhoneNumberParams($entityType, $foreignJoinAlias, $foreignJoinMiddleAlias)
            );

        $dataDefs = AttributeDefs
            ::create($name . 'Data')
            ->withType(AttributeType::JSON_ARRAY)
            ->withNotStorable()
            ->withParamsMerged([
                AttributeParam::NOT_EXPORTABLE => true,
                'isPhoneNumberData' => true,
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

        $numericAttribute = AttributeDefs
            ::create($name . 'Numeric')
            ->withType(AttributeType::VARCHAR)
            ->withNotStorable()
            ->withParamsMerged(
                $this->getNumericParams($entityType)
            );

        $relationDefs = RelationDefs
            ::create('phoneNumbers')
            ->withType(RelationType::MANY_MANY)
            ->withForeignEntityType(PhoneNumber::ENTITY_TYPE)
            ->withRelationshipName('entityPhoneNumber')
            ->withMidKeys('entityId', 'phoneNumberId')
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
            ->withAttribute($numericAttribute)
            ->withRelation($relationDefs);
    }

    /**
     * @return array<string, mixed>
     */
    private function getPhoneNumberParams(
        string $entityType,
        string $foreignJoinAlias,
        string $foreignJoinMiddleAlias,
    ): array {

        return [
            'select' => [
                "select" => "phoneNumbers.name",
                'leftJoins' => [['phoneNumbers', 'phoneNumbers', ['primary' => true]]],
            ],
            'selectForeign' => [
                "select" => "$foreignJoinAlias.name",
                'leftJoins' => [
                    [
                        'EntityPhoneNumber',
                        $foreignJoinMiddleAlias,
                        [
                            "$foreignJoinMiddleAlias.entityId:" => "{alias}.id",
                            "$foreignJoinMiddleAlias.primary" => true,
                            "$foreignJoinMiddleAlias.deleted" => false,
                        ]
                    ],
                    [
                        PhoneNumber::ENTITY_TYPE,
                        $foreignJoinAlias,
                        [
                            "$foreignJoinAlias.id:" => "$foreignJoinMiddleAlias.phoneNumberId",
                            "$foreignJoinAlias.deleted" => false,
                        ]
                    ]
                ],
            ],
            'fieldType' => FieldType::PHONE,
            'where' => [
                'LIKE' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'phoneNumber',
                                    'phoneNumber',
                                    [
                                        'phoneNumber.id:' => 'phoneNumberId',
                                        'phoneNumber.deleted' => false,
                                    ],
                                ],
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                'phoneNumber.name*' => '{value}',
                            ],
                        ],
                    ],
                ],
                'NOT LIKE' => [
                    'whereClause' => [
                        'id!=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'phoneNumber',
                                    'phoneNumber',
                                    [
                                        'phoneNumber.id:' => 'phoneNumberId',
                                        'phoneNumber.deleted' => false,
                                    ],
                                ],
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                'phoneNumber.name*' => '{value}',
                            ],
                        ],
                    ],
                ],
                '=' => [
                    'leftJoins' => [['phoneNumbers', 'phoneNumbersMultiple']],
                    'whereClause' => [
                        'phoneNumbersMultiple.name=' => '{value}',
                    ]
                ],
                '<>' => [
                    'whereClause' => [
                        'id!=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'phoneNumber',
                                    'phoneNumber',
                                    [
                                        'phoneNumber.id:' => 'phoneNumberId',
                                        'phoneNumber.deleted' => false,
                                    ],
                                ],
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                'phoneNumber.name' => '{value}',
                            ],
                        ],
                    ],
                ],
                'IN' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'phoneNumber',
                                    'phoneNumber',
                                    [
                                        'phoneNumber.id:' => 'phoneNumberId',
                                        'phoneNumber.deleted' => false,
                                    ],
                                ],
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                'phoneNumber.name' => '{value}',
                            ],
                        ],
                    ],
                ],
                'NOT IN' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'phoneNumber',
                                    'phoneNumber',
                                    [
                                        'phoneNumber.id:' => 'phoneNumberId',
                                        'phoneNumber.deleted' => false,
                                    ],
                                ],
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                'phoneNumber.name!=' => '{value}',
                            ],
                        ],
                    ],
                ],
                'IS NULL' => [
                    'leftJoins' => [['phoneNumbers', 'phoneNumbersMultiple']],
                    'whereClause' => [
                        'phoneNumbersMultiple.name=' => null,
                    ]
                ],
                'IS NOT NULL' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => 'EntityPhoneNumber',
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
                    ['phoneNumbers.name', '{direction}'],
                ],
                'leftJoins' => [['phoneNumbers', 'phoneNumbers', ['primary' => true]]],
                'additionalSelect' => ['phoneNumbers.name'],
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
                'select' => 'phoneNumbers.optOut',
                'leftJoins' => [['phoneNumbers', 'phoneNumbers', ['primary' => true]]],
            ],
            'selectForeign' => [
                'select' => "$foreignJoinAlias.optOut",
                'leftJoins' => [
                    [
                        'EntityPhoneNumber',
                        $foreignJoinMiddleAlias,
                        [
                            "$foreignJoinMiddleAlias.entityId:" => "{alias}.id",
                            "$foreignJoinMiddleAlias.primary" => true,
                            "$foreignJoinMiddleAlias.deleted" => false,
                        ]
                    ],
                    [
                        PhoneNumber::ENTITY_TYPE,
                        $foreignJoinAlias,
                        [
                            "$foreignJoinAlias.id:" => "$foreignJoinMiddleAlias.phoneNumberId",
                            "$foreignJoinAlias.deleted" => false,
                        ]
                    ]
                ],
            ],
            'where' => [
                '= TRUE' => [
                    'whereClause' => [
                        ['phoneNumbers.optOut=' => true],
                        ['phoneNumbers.optOut!=' => null],
                    ],
                    'leftJoins' => [['phoneNumbers', 'phoneNumbers', ['primary' => true]]],
                ],
                '= FALSE' => [
                    'whereClause' => [
                        'OR' => [
                            ['phoneNumbers.optOut=' => false],
                            ['phoneNumbers.optOut=' => null],
                        ]
                    ],
                    'leftJoins' => [['phoneNumbers', 'phoneNumbers', ['primary' => true]]],
                ]
            ],
            'order' => [
                'order' => [
                    ['phoneNumbers.optOut', '{direction}'],
                ],
                'leftJoins' => [['phoneNumbers', 'phoneNumbers', ['primary' => true]]],
                'additionalSelect' => ['phoneNumbers.optOut'],
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
                'select' => 'phoneNumbers.invalid',
                'leftJoins' => [['phoneNumbers', 'phoneNumbers', ['primary' => true]]],
            ],
            'selectForeign' => [
                'select' => "$foreignJoinAlias.invalid",
                'leftJoins' => [
                    [
                        'EntityPhoneNumber',
                        $foreignJoinMiddleAlias,
                        [
                            "$foreignJoinMiddleAlias.entityId:" => "{alias}.id",
                            "$foreignJoinMiddleAlias.primary" => true,
                            "$foreignJoinMiddleAlias.deleted" => false,
                        ]
                    ],
                    [
                        PhoneNumber::ENTITY_TYPE,
                        $foreignJoinAlias,
                        [
                            "$foreignJoinAlias.id:" => "$foreignJoinMiddleAlias.phoneNumberId",
                            "$foreignJoinAlias.deleted" => false,
                        ]
                    ]
                ],
            ],
            'where' => [
                '= TRUE' => [
                    'whereClause' => [
                        ['phoneNumbers.invalid=' => true],
                        ['phoneNumbers.invalid!=' => null],
                    ],
                    'leftJoins' => [['phoneNumbers', 'phoneNumbers', ['primary' => true]]],
                ],
                '= FALSE' => [
                    'whereClause' => [
                        'OR' => [
                            ['phoneNumbers.invalid=' => false],
                            ['phoneNumbers.invalid=' => null],
                        ]
                    ],
                    'leftJoins' => [['phoneNumbers', 'phoneNumbers', ['primary' => true]]],
                ]
            ],
            'order' => [
                'order' => [
                    ['phoneNumbers.invalid', '{direction}'],
                ],
                'leftJoins' => [['phoneNumbers', 'phoneNumbers', ['primary' => true]]],
                'additionalSelect' => ['phoneNumbers.invalid'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getNumericParams(string $entityType): array
    {
        return [
            AttributeParam::NOT_EXPORTABLE => true,
            'where' => [
                'LIKE' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'phoneNumber',
                                    'phoneNumber',
                                    [
                                        'phoneNumber.id:' => 'phoneNumberId',
                                        'phoneNumber.deleted' => false,
                                    ],
                                ],
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                'phoneNumber.numeric*' => '{value}',
                            ],
                        ],
                    ],
                ],
                'NOT LIKE' => [
                    'whereClause' => [
                        'id!=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'phoneNumber',
                                    'phoneNumber',
                                    [
                                        'phoneNumber.id:' => 'phoneNumberId',
                                        'phoneNumber.deleted' => false,
                                    ],
                                ]
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                'phoneNumber.numeric*' => '{value}',
                            ],
                        ],
                    ],
                ],
                '=' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'phoneNumber',
                                    'phoneNumber',
                                    [
                                        'phoneNumber.id:' => 'phoneNumberId',
                                        'phoneNumber.deleted' => false,
                                    ],
                                ],
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                'phoneNumber.numeric' => '{value}',
                            ],
                        ],
                    ],
                ],
                '<>' => [
                    'whereClause' => [
                        'id!=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'phoneNumber',
                                    'phoneNumber',
                                    [
                                        'phoneNumber.id:' => 'phoneNumberId',
                                        'phoneNumber.deleted' => false,
                                    ],
                                ],
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                'phoneNumber.numeric' => '{value}',
                            ],
                        ],
                    ],
                ],
                'IN' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'phoneNumber',
                                    'phoneNumber',
                                    [
                                        'phoneNumber.id:' => 'phoneNumberId',
                                        'phoneNumber.deleted' => false,
                                    ],
                                ],
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                'phoneNumber.numeric' => '{value}',
                            ],
                        ],
                    ],
                ],
                'NOT IN' => [
                    'whereClause' => [
                        'id!=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'joins' => [
                                [
                                    'phoneNumber',
                                    'phoneNumber',
                                    [
                                        'phoneNumber.id:' => 'phoneNumberId',
                                        'phoneNumber.deleted' => false,
                                    ],
                                ],
                            ],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                                'phoneNumber.numeric' => '{value}',
                            ],
                        ],
                    ],
                ],
                'IS NULL' => [
                    'leftJoins' => [['phoneNumbers', 'phoneNumbersMultiple']],
                    'whereClause' => [
                        'phoneNumbersMultiple.numeric=' => null,
                    ]
                ],
                'IS NOT NULL' => [
                    'whereClause' => [
                        'id=s' => [
                            'from' => 'EntityPhoneNumber',
                            'select' => ['entityId'],
                            'whereClause' => [
                                Attribute::DELETED => false,
                                'entityType' => $entityType,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
