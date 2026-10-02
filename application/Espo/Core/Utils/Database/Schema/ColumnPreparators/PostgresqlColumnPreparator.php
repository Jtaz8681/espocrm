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

namespace Espo\Core\Utils\Database\Schema\ColumnPreparators;

use Doctrine\DBAL\Types\Types;
use Espo\Core\Utils\Database\Schema\Column;
use Espo\Core\Utils\Database\Schema\ColumnPreparator;
use Espo\Core\Utils\Util;
use Espo\ORM\Defs\AttributeDefs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Entity;

class PostgresqlColumnPreparator implements ColumnPreparator
{
    private const PARAM_DB_TYPE = AttributeParam::DB_TYPE;
    private const PARAM_DEFAULT = AttributeParam::DEFAULT;
    private const PARAM_NOT_NULL = AttributeParam::NOT_NULL;
    private const PARAM_AUTOINCREMENT = 'autoincrement';
    private const PARAM_PRECISION = 'precision';
    private const PARAM_SCALE = 'scale';
    private const string PARAM_STORE_ARRAY_VALUES = 'storeArrayValues';

    /** @var string[] */
    private array $textTypeList = [
        Entity::TEXT,
        Entity::JSON_OBJECT,
        Entity::JSON_ARRAY,
    ];

    /** @var array<string, string> */
    private array $columnTypeMap = [
        Entity::BOOL => Types::BOOLEAN,
        Entity::INT => Types::INTEGER,
        Entity::VARCHAR => Types::STRING,
        // DBAL reverse engineers as blob.
        Types::BINARY => Types::BLOB,
    ];

    public function __construct() {}

    public function prepare(AttributeDefs $defs): Column
    {
        $dbType = $defs->getParam(self::PARAM_DB_TYPE);
        $type = $defs->getType();
        $length = $defs->getLength();
        $default = $defs->getParam(self::PARAM_DEFAULT);
        $notNull = $defs->getParam(self::PARAM_NOT_NULL);
        $autoincrement = $defs->getParam(self::PARAM_AUTOINCREMENT);
        $precision = $defs->getParam(self::PARAM_PRECISION);
        $scale = $defs->getParam(self::PARAM_SCALE);

        $columnType = $dbType ?? $type;

        if (in_array($type, $this->textTypeList) && !$dbType) {
            $columnType = Types::TEXT;
        }

        $columnType = $this->columnTypeMap[$columnType] ?? $columnType;

        $columnName = Util::toUnderScore($defs->getName());

        $column = Column::create($columnName, strtolower($columnType));

        if ($length !== null) {
            $column = $column->withLength($length);
        }

        if ($default !== null) {
            $column = $column->withDefault($default);
        }

        if ($notNull !== null) {
            $column = $column->withNotNull($notNull);
        }

        if ($autoincrement !== null) {
            $column = $column->withAutoincrement($autoincrement);
        }

        if ($precision !== null) {
            $column = $column->withPrecision($precision);
        }

        if ($scale !== null) {
            $column = $column->withScale($scale);
        }

        switch ($type) {
            case Entity::TEXT:
                $column = $column->withDefault(null);

                break;

            case Entity::JSON_ARRAY:
                $default = is_array($default) ? json_encode($default) : null;

                if ($defs->getParam(self::PARAM_STORE_ARRAY_VALUES)) {
                    $default = json_encode([]);
                }

                $column = $column->withDefault($default);

                break;

            case Entity::BOOL:
                $default = intval($default ?? false);

                $column = $column->withDefault($default);

                break;
        }

        if ($type !== Entity::ID && $autoincrement) {
            $column = $column
                ->withNotNull()
                ->withUnsigned();
        }

        return $column;

        // @todo Revise. Comparator would detect the column as changed if charset is set.
        /*if (
            !in_array($columnType, [
                Types::STRING,
                Types::TEXT,
            ])
        ) {
            return $column;
        }

        return $column->withCharset('UTF8');*/
    }
}
