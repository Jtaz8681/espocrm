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

use Espo\Core\Utils\Config;
use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Orm\FieldConverter;
use Espo\ORM\Defs\FieldDefs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\ORM\Type\AttributeType;

/**
 * @noinspection PhpUnused
 */
class PersonName implements FieldConverter
{
    private const FORMAT_LAST_FIRST = 'lastFirst';
    private const FORMAT_LAST_FIRST_MIDDLE = 'lastFirstMiddle';
    private const FORMAT_FIRST_MIDDLE_LAST = 'firstMiddleLast';

    public function __construct(private Config $config) {}

    public function convert(FieldDefs $fieldDefs, string $entityType): EntityDefs
    {
        $format = $this->config->get('personNameFormat');

        $name = $fieldDefs->getName();
        $firstName = 'first' . ucfirst($name);
        $lastName = 'last' . ucfirst($name);
        $middleName = 'middle' . ucfirst($name);

        $subList = match ($format) {
            self::FORMAT_LAST_FIRST => [$lastName, ' ', $firstName],
            self::FORMAT_LAST_FIRST_MIDDLE => [$lastName, ' ', $firstName, ' ', $middleName],
            self::FORMAT_FIRST_MIDDLE_LAST => [$firstName, ' ', $middleName, ' ', $lastName],
            default => [$firstName, ' ', $lastName],
        };

        if (
            $format === self::FORMAT_LAST_FIRST_MIDDLE ||
            $format === self::FORMAT_LAST_FIRST
        ) {
            $orderBy1Field = $lastName;
            $orderBy2Field = $firstName;
        } else {
            $orderBy1Field = $firstName;
            $orderBy2Field = $lastName;
        }

        $fullList = [];
        $whereItems = [];

        foreach ($subList as $subFieldName) {
            $fieldNameTrimmed = trim($subFieldName);

            if (empty($fieldNameTrimmed)) {
                $fullList[] = "'" . $subFieldName . "'";

                continue;
            }

            $fullList[] = $fieldNameTrimmed;
            $whereItems[] = $fieldNameTrimmed;
        }

        $whereItems[] = "CONCAT:($firstName, ' ', $lastName)";
        $whereItems[] = "CONCAT:($lastName, ' ', $firstName)";

        if ($format === self::FORMAT_FIRST_MIDDLE_LAST) {
            $whereItems[] = "CONCAT:($firstName, ' ', $middleName, ' ', $lastName)";
        } else if ($format === self::FORMAT_LAST_FIRST_MIDDLE) {
            $whereItems[] = "CONCAT:($lastName, ' ', $firstName, ' ', $middleName)";
        }

        $nameColumns = [
            $firstName,
            $middleName,
            $lastName,
        ];

        $selectExpression = $this->getSelect($fullList);
        $selectForeignExpression = $this->getSelect($fullList, '{alias}');

        if (
            $format === self::FORMAT_FIRST_MIDDLE_LAST ||
            $format === self::FORMAT_LAST_FIRST_MIDDLE
        ) {
            $selectExpression = "REPLACE:($selectExpression, '  ', ' ')";
            $selectForeignExpression = "REPLACE:($selectForeignExpression, '  ', ' ')";
        }

        $attributeDefs = AttributeDefs::create($name)
            ->withType(AttributeType::VARCHAR)
            ->withNotStorable()
            ->withParamsMerged([
                'select' => [
                    'select' => $selectExpression,
                ],
                'selectForeign' => [
                    'select' => $selectForeignExpression,
                ],
                'where' => [
                    'LIKE' => [
                        'whereClause' => [
                            'OR' => array_fill_keys(
                                array_map(fn ($item) => $item . '*', $whereItems),
                                '{value}'
                            ),
                        ],
                    ],
                    'NOT LIKE' => [
                        'whereClause' => [
                            'AND' => array_fill_keys(
                                array_map(fn ($item) => $item . '!*', $whereItems),
                                '{value}'
                            ),
                        ],
                    ],
                    '=' => [
                        'whereClause' => [
                            'OR' => array_fill_keys($whereItems, '{value}'),
                        ],
                    ],
                    '<>' => [
                        'whereClause' => [
                            'AND' => array_fill_keys(
                                array_map(fn ($item) => $item . '!=', $whereItems),
                                '{value}'
                            ),
                        ],
                    ],
                    'IN' => [
                        // Not supported.
                        'whereClause' => [
                            'false:' => null,
                        ]
                    ],
                    'NOT IN' => [
                        // Not supported.
                        'whereClause' => [
                            'false:' => null,
                        ]
                    ],
                    'IS NULL' => [
                        'whereClause' => [
                            'AND' => array_fill_keys(
                                array_map(fn ($item) => $item . '=', $nameColumns),
                                null
                            ),
                        ]

                    ],
                    'IS NOT NULL' => [
                        'whereClause' => [
                            'OR' => array_fill_keys(
                                array_map(fn ($item) => $item . '!=', $nameColumns),
                                null
                            ),
                        ]
                    ],
                ],
                'order' => [
                    'order' => [
                        [$orderBy1Field, '{direction}'],
                        [$orderBy2Field, '{direction}'],
                    ],
                ],
            ]);

        $dependeeAttributeList = $fieldDefs->getParam(FieldParam::DEPENDEE_ATTRIBUTE_LIST);

        if ($dependeeAttributeList) {
            $attributeDefs = $attributeDefs->withParam(AttributeParam::DEPENDEE_ATTRIBUTE_LIST, $dependeeAttributeList);
        }

        return EntityDefs::create()
            ->withAttribute($attributeDefs);
    }

    /**
     * @param string[] $fullList
     */
    private function getSelect(array $fullList, ?string $alias = null): string
    {
        foreach ($fullList as &$item) {
            $rowItem = trim($item, " '");

            if (empty($rowItem)) {
                continue;
            }

            if ($alias) {
                $item = $alias . '.' . $item;
            }

            $item = "IFNULL:($item, '')";
        }

        return "NULLIF:(TRIM:(CONCAT:(" . implode(", ", $fullList) . ")), '')";
    }
}
