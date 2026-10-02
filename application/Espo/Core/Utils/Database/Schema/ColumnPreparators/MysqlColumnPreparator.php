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
use Espo\Core\Utils\Database\Dbal\Types\LongtextType;
use Espo\Core\Utils\Database\Dbal\Types\MediumtextType;
use Espo\Core\Utils\Database\Helper;
use Espo\Core\Utils\Database\Schema\Column;
use Espo\Core\Utils\Database\Schema\ColumnPreparator;
use Espo\Core\Utils\Util;
use Espo\ORM\Defs\AttributeDefs;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Entity;

class MysqlColumnPreparator implements ColumnPreparator
{
    private const PARAM_DB_TYPE = AttributeParam::DB_TYPE;
    private const PARAM_DEFAULT = AttributeParam::DEFAULT;
    private const PARAM_NOT_NULL = AttributeParam::NOT_NULL;
    private const PARAM_AUTOINCREMENT = 'autoincrement';
    private const PARAM_PRECISION = 'precision';
    private const PARAM_SCALE = 'scale';
    private const PARAM_BINARY = 'binary';
    private const string PARAM_STORE_ARRAY_VALUES = 'storeArrayValues';

    public const TYPE_MYSQL = 'MySQL';
    public const TYPE_MARIADB = 'MariaDB';

    private const MB4_INDEX_LENGTH_LIMIT = 3072;
    private const DEFAULT_INDEX_LIMIT = 1000;

    /** @var string[] */
    private array $mediumTextTypeList = [
        Entity::TEXT,
        Entity::JSON_OBJECT,
        Entity::JSON_ARRAY,
    ];

    /** @var array<string, string> */
    private array $columnTypeMap = [
        Entity::BOOL => Types::BOOLEAN,
        Entity::INT => Types::INTEGER,
        Entity::VARCHAR => Types::STRING,
    ];

    private ?int $maxIndexLength = null;

    public function __construct(
        private Helper $helper
    ) {}

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
        $binary = $defs->getParam(self::PARAM_BINARY);

        $columnType = $dbType ?? $type;

        if (in_array($type, $this->mediumTextTypeList) && !$dbType) {
            $columnType = MediumtextType::NAME;
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

        $mb3 = false;

        switch ($type) {
            case Entity::ID:
            case Entity::FOREIGN_ID:
            case Entity::FOREIGN_TYPE:
                $mb3 = $this->getMaxIndexLength() < self::MB4_INDEX_LENGTH_LIMIT;

                break;

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

        if (
            !in_array($columnType, [
                Types::STRING,
                Types::TEXT,
                MediumtextType::NAME,
                LongtextType::NAME,
            ])
        ) {
            return $column;
        }

        $collation = $binary ?
            'utf8mb4_bin' :
            'utf8mb4_unicode_ci';

        $charset = 'utf8mb4';

        if ($mb3) {
            $collation = $binary ?
                'utf8mb3_bin' :
                'utf8mb3_unicode_ci';

            $charset = 'utf8mb3';
        }

        return $column
            ->withCollation($collation)
            ->withCharset($charset);
    }

    private function getMaxIndexLength(): int
    {
        if (!isset($this->maxIndexLength)) {
            $this->maxIndexLength = $this->detectMaxIndexLength();
        }

        return $this->maxIndexLength;
    }

    /**
     * Get maximum index length.
     */
    private function detectMaxIndexLength(): int
    {
        $tableEngine = $this->getTableEngine();

        if (!$tableEngine) {
            return self::DEFAULT_INDEX_LIMIT;
        }

        return match ($tableEngine) {
            'InnoDB' => 3072,
            default => 1000,
        };
    }

    /**
     * Get a table or default engine.
     */
    private function getTableEngine(): ?string
    {
        $databaseType = $this->helper->getType();

        if (!in_array($databaseType, [self::TYPE_MYSQL, self::TYPE_MARIADB])) {
            return null;
        }

        $query = "SHOW TABLE STATUS WHERE Engine = 'MyISAM'";

        $vars = [];

        $pdo = $this->helper->getPDO();

        $sth = $pdo->prepare($query);
        $sth->execute($vars);

        $result = $sth->fetchColumn();

        if (!empty($result)) {
            return 'MyISAM';
        }

        return 'InnoDB';
    }
}
