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

use Doctrine\DBAL\Types\Types;
use Espo\Core\Currency\ConfigDataProvider;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Database\Orm\Defs\AttributeDefs;
use Espo\Core\Utils\Database\Orm\Defs\EntityDefs;
use Espo\Core\Utils\Database\Orm\FieldConverter;
use Espo\ORM\Defs\FieldDefs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Type\AttributeType;

class Currency implements FieldConverter
{
    private const DEFAULT_PRECISION = 13;
    private const DEFAULT_SCALE = 4;

    public function __construct(
        private Config $config,
        private ConfigDataProvider $configDataProvider
    ) {}

    public function convert(FieldDefs $fieldDefs, string $entityType): EntityDefs
    {
        $name = $fieldDefs->getName();

        $amountDefs = AttributeDefs::create($name)
            ->withType(AttributeType::FLOAT)
            ->withParamsMerged([
                'attributeRole' => 'value',
                'fieldType' => FieldType::CURRENCY,
            ]);

        $currencyDefs = AttributeDefs::create($name . 'Currency')
            ->withType(AttributeType::VARCHAR)
            ->withParamsMerged([
                'attributeRole' => 'currency',
                'fieldType' => FieldType::CURRENCY,
            ]);

        $convertedDefs = null;

        if ($fieldDefs->getParam(FieldParam::DECIMAL)) {
            $dbType = $fieldDefs->getParam(FieldParam::DB_TYPE) ?? Types::DECIMAL;
            $precision = $fieldDefs->getParam(FieldParam::PRECISION) ?? self::DEFAULT_PRECISION;
            $scale = $fieldDefs->getParam(FieldParam::SCALE) ?? self::DEFAULT_SCALE;

            $amountDefs = $amountDefs
                ->withType(AttributeType::VARCHAR)
                ->withDbType($dbType)
                ->withParam(AttributeParam::PRECISION, $precision)
                ->withParam(AttributeParam::SCALE, $scale);

            $defaultValue = $fieldDefs->getParam(AttributeParam::DEFAULT);

            if (is_int($defaultValue) || is_float($defaultValue)) {
                $defaultValue = number_format($defaultValue, $scale, '.', '');

                $amountDefs = $amountDefs->withParam(AttributeParam::DEFAULT, $defaultValue);
            }
        }

        if ($fieldDefs->isNotStorable()) {
            $amountDefs = $amountDefs->withNotStorable();
            $currencyDefs = $currencyDefs->withNotStorable();
        }

        if (!$fieldDefs->isNotStorable()) {
            [$amountDefs, $convertedDefs] = ($this->config->get('currencyNoJoinMode') !== false) ?
                $this->applyNoJoinMode($fieldDefs, $amountDefs) :
                $this->applyJoinMode($fieldDefs, $amountDefs, $entityType);
        }

        $entityDefs = EntityDefs::create()
            ->withAttribute($amountDefs)
            ->withAttribute($currencyDefs);

        if ($convertedDefs) {
            $entityDefs = $entityDefs->withAttribute($convertedDefs);
        }

        return $entityDefs;
    }

    /**
     * @return array{AttributeDefs, AttributeDefs}
     */
    private function applyNoJoinMode(FieldDefs $fieldDefs, AttributeDefs $amountDefs): array
    {
        $name = $fieldDefs->getName();

        $currencyAttribute = $name . 'Currency';

        $defaultCurrency = $this->configDataProvider->getDefaultCurrency();

        $rates = $this->configDataProvider->getCurrencyList();

        $expr = Expr::multiply(
            Expr::column($name),
            Expr::if(
                Expr::equal(Expr::column($currencyAttribute), $defaultCurrency),
                1.0,
                $this->buildExpression($currencyAttribute, $rates)
            )
        )->getValue();

        $exprForeign = Expr::multiply(
            Expr::column("ALIAS.{$name}"),
            Expr::if(
                Expr::equal(Expr::column("ALIAS.{$name}Currency"), $defaultCurrency),
                1.0,
                $this->buildExpression("ALIAS.{$name}Currency", $rates)
            )
        )->getValue();

        $exprForeign = str_replace('ALIAS', '{alias}', $exprForeign);

        $convertedDefs = AttributeDefs::create($name . 'Converted')
            ->withType(AttributeType::FLOAT)
            ->withParamsMerged([
                'select' => [
                    'select' => $expr,
                ],
                'selectForeign' => [
                    'select' => $exprForeign,
                ],
                'where' => [
                    "=" => [
                        'whereClause' => [
                            $expr . '=' => '{value}',
                        ],
                    ],
                    ">" => [
                        'whereClause' => [
                            $expr . '>' => '{value}',
                        ],
                    ],
                    "<" => [
                        'whereClause' => [
                            $expr . '<' => '{value}',
                        ],
                    ],
                    ">=" => [
                        'whereClause' => [
                            $expr . '>=' => '{value}',
                        ],
                    ],
                    "<=" => [
                        'whereClause' => [
                            $expr . '<=' => '{value}',
                        ],
                    ],
                    "<>" => [
                        'whereClause' => [
                            $expr . '!=' => '{value}',
                        ],
                    ],
                    "IS NULL" => [
                        'whereClause' => [
                            $expr . '=' => null,
                        ],
                    ],
                    "IS NOT NULL" => [
                        'whereClause' => [
                            $expr . '!=' => null,
                        ],
                    ],
                ],
                AttributeParam::NOT_STORABLE => true,
                'order' => [
                    'order' => [
                        [$expr, '{direction}'],
                    ],
                ],
                'attributeRole' => 'valueConverted',
                'fieldType' => FieldType::CURRENCY,
            ]);

        return [$amountDefs, $convertedDefs];
    }

    /**
     * @param string[] $rates
     */
    private function buildExpression(string $currencyAttribute, array $rates): Expr|float
    {
        if ($rates === []) {
            return 0.0;
        }

        $currency = array_shift($rates);

        return Expr::if(
            Expr::equal(Expr::column($currencyAttribute), $currency),
            Expr::create("CURRENCY_RATE:('$currency')"),
            $this->buildExpression($currencyAttribute, $rates)
        );
    }

    /**
     * @return array{AttributeDefs, AttributeDefs}
     */
    private function applyJoinMode(FieldDefs $fieldDefs, AttributeDefs $amountDefs, string $entityType): array
    {
        $name = $fieldDefs->getName();

        $alias = $name . 'CurrencyRecordRate';
        $leftJoins = [
            [
                'Currency',
                $alias,
                [$alias . '.id:' => $name . 'Currency'],
            ]
        ];
        $foreignCurrencyAlias = "{$alias}{$entityType}{alias}Foreign";
        $mulExpression = "MUL:({$name}, {$alias}.rate)";

        $amountDefs = $amountDefs->withParamsMerged([
            'order' => [
                'order' => [
                    [$mulExpression, '{direction}'],
                ],
                'leftJoins' => $leftJoins,
                'additionalSelect' => ["{$alias}.rate"],
            ]
        ]);

        $convertedDefs = AttributeDefs::create($name . 'Converted')
            ->withType(AttributeType::FLOAT)
            ->withParamsMerged([
                'select' => [
                    'select' => $mulExpression,
                    'leftJoins' => $leftJoins,
                ],
                'selectForeign' => [
                    'select' => "MUL:({alias}.{$name}, {$foreignCurrencyAlias}.rate)",
                    'leftJoins' => [
                        [
                            'Currency',
                            $foreignCurrencyAlias,
                            [$foreignCurrencyAlias . '.id:' => "{alias}.{$name}Currency"]
                        ]
                    ],
                ],
                'where' => [
                    "=" => [
                        'whereClause' => [$mulExpression . '=' => '{value}'],
                        'leftJoins' => $leftJoins,
                    ],
                    ">" => [
                        'whereClause' => [$mulExpression . '>' => '{value}'],
                        'leftJoins' => $leftJoins,
                    ],
                    "<" => [
                        'whereClause' => [$mulExpression . '<' => '{value}'],
                        'leftJoins' => $leftJoins,
                    ],
                    ">=" => [
                        'whereClause' => [$mulExpression . '>=' => '{value}'],
                        'leftJoins' => $leftJoins,
                    ],
                    "<=" => [
                        'whereClause' => [$mulExpression . '<=' => '{value}'],
                        'leftJoins' => $leftJoins,
                    ],
                    "<>" => [
                        'whereClause' => [$mulExpression . '!=' => '{value}'],
                        'leftJoins' => $leftJoins,
                    ],
                    "IS NULL" => [
                        'whereClause' => [$name . '=' => null],
                    ],
                    "IS NOT NULL" => [
                        'whereClause' => [$name . '!=' => null],
                    ],
                ],
                AttributeParam::NOT_STORABLE => true,
                'order' => [
                    'order' => [
                        [$mulExpression, '{direction}'],
                    ],
                    'leftJoins' => $leftJoins,
                    'additionalSelect' => ["{$alias}.rate"],
                ],
                'attributeRole' => 'valueConverted',
                'fieldType' => FieldType::CURRENCY,
            ]);

        return [$amountDefs, $convertedDefs];
    }
}
