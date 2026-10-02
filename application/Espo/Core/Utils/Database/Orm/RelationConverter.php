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

namespace Espo\Core\Utils\Database\Orm;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Database\Orm\LinkConverters\BelongsTo;
use Espo\Core\Utils\Database\Orm\LinkConverters\BelongsToParent;
use Espo\Core\Utils\Database\Orm\LinkConverters\HasChildren;
use Espo\Core\Utils\Database\Orm\LinkConverters\HasMany;
use Espo\Core\Utils\Database\Orm\LinkConverters\HasOne;
use Espo\Core\Utils\Database\Orm\LinkConverters\ManyMany;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Util;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs\Params\AttributeParam;
use Espo\ORM\Defs\Params\EntityParam;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Defs\RelationDefs;
use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;

use RuntimeException;

class RelationConverter
{
    private const DEFAULT_VARCHAR_LENGTH = 255;

    /**
     * Parameters to merge from both sides.
     *
     * @var string[]
     */
    private $mergeParams = [
        RelationParam::RELATION_NAME,
        RelationParam::CONDITIONS,
        RelationParam::ADDITIONAL_COLUMNS,
        'noJoin',
        RelationParam::INDEXES,
    ];

    /** @var string[] */
    private $manyMergeParams = [
        RelationParam::ORDER_BY,
        RelationParam::ORDER,
    ];

    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory,
        private Log $log
    ) {}

    /**
     * @param string $name
     * @param array<string, mixed> $params
     * @param string $entityType
     * @return ?array<string, mixed>
     */
    public function process(string $name, array $params, string $entityType): ?array
    {
        $foreignEntityType = $params[RelationParam::ENTITY] ?? null;
        $foreignLinkName = $params[RelationParam::FOREIGN] ?? null;

        /** @var ?array<string, mixed> $foreignParams */
        $foreignParams = $foreignEntityType && $foreignLinkName ?
            $this->metadata->get(['entityDefs', $foreignEntityType, 'links', $foreignLinkName]) :
            null;

        /** @var ?string $relationshipName */
        $relationshipName = $params[RelationParam::RELATION_NAME] ?? null;

        if ($relationshipName) {
            $relationshipName = lcfirst($relationshipName);
            $params[RelationParam::RELATION_NAME] = $relationshipName;
        }

        $linkType = $params[RelationParam::TYPE] ?? null;
        $foreignLinkType = $foreignParams ? $foreignParams[RelationParam::TYPE] : null;

        if (!$linkType) {
            $this->log->warning("Link $entityType.$name has no type.");

            return null;
        }

        $params['hasField'] = (bool) $this->metadata
            ->get(['entityDefs', $entityType, 'fields', $name]);

        $relationDefs = RelationDefs::fromRaw($params, $name);

        $converter = $this->createLinkConverter($relationshipName, $linkType, $foreignLinkType);

        $convertedEntityDefs = $converter->convert($relationDefs, $entityType);

        $raw = $convertedEntityDefs->toAssoc();

        if (isset($raw[EntityParam::RELATIONS][$name])) {
            $defs = &$raw[EntityParam::RELATIONS][$name];

            $this->mergeParams($defs, $params, $foreignParams ?? [], $linkType);
            $this->correct($defs);

            if ($params[RelationParam::CASCADE_REMOVAL] ?? false) {
                $defs[RelationParam::CASCADE_REMOVAL] = true;
            }
        }

        return [$entityType => $raw];
    }

    private function createLinkConverter(?string $relationship, string $type, ?string $foreignType): LinkConverter
    {
        $className = $this->getLinkConverterClassName($relationship, $type, $foreignType);

        return $this->injectableFactory->create($className);
    }

    /**
     * @return class-string<LinkConverter>
     */
    private function getLinkConverterClassName(?string $relationship, string $type, ?string $foreignType): string
    {
        if ($relationship) {
            /** @var class-string<LinkConverter> $className */
            $className = $this->metadata->get(['app', 'relationships', $relationship, 'converterClassName']);

            if ($className) {
                return $className;
            }
        }

        if ($type === RelationType::HAS_MANY && $foreignType === RelationType::HAS_MANY) {
            return ManyMany::class;
        }

        if ($type === RelationType::HAS_MANY) {
            return HasMany::class;
        }

        if ($type === RelationType::HAS_CHILDREN) {
            return HasChildren::class;
        }

        if ($type === RelationType::HAS_ONE) {
            return HasOne::class;
        }

        if ($type === RelationType::BELONGS_TO) {
            return BelongsTo::class;
        }

        if ($type === RelationType::BELONGS_TO_PARENT) {
            return BelongsToParent::class;
        }

        throw new RuntimeException("Unsupported link type '$type'.");
    }

    /**
     * @param array<string, mixed> $relationDefs
     * @param array<string, mixed> $params
     * @param array<string, mixed> $foreignParams
     */
    private function mergeParams(array &$relationDefs, array $params, array $foreignParams, string $linkType): void
    {
        $mergeParams = $this->mergeParams;

        if (
            $linkType === RelationType::HAS_MANY ||
            $linkType === RelationType::HAS_CHILDREN
        ) {
            $mergeParams = array_merge($mergeParams, $this->manyMergeParams);
        }

        foreach ($mergeParams as $name) {
            $additionalParam = $this->getMergedParam($name, $params, $foreignParams);

            if ($additionalParam === null) {
                continue;
            }

            $relationDefs[$name] = $additionalParam;
        }
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $foreignParams
     * @return array<string, mixed>|scalar|null
     */
    private function getMergedParam(string $name, array $params, array $foreignParams): mixed
    {
        $value = $params[$name] ?? null;
        $foreignValue = $foreignParams[$name] ?? null;

        if ($value !== null && $foreignValue !== null) {
            if (!empty($value) && !is_array($value)) {
                return $value;
            }

            if (!empty($foreignValue) && !is_array($foreignValue)) {
                return $foreignValue;
            }

            /** @var array<int|string, mixed> $value */
            /** @var array<int|string, mixed> $foreignValue */

            /** @var array<string, mixed> */
            return Util::merge($value, $foreignValue);
        }

        if (isset($value)) {
            return $value;
        }

        if (isset($foreignValue)) {
            return $foreignValue;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $relationDefs
     */
    private function correct(array &$relationDefs): void
    {
        if (
            isset($relationDefs[RelationParam::ORDER]) &&
            is_string($relationDefs[RelationParam::ORDER])
        ) {
            $relationDefs[RelationParam::ORDER] = strtoupper($relationDefs[RelationParam::ORDER]);
        }

        if (!isset($relationDefs[RelationParam::ADDITIONAL_COLUMNS])) {
            return;
        }

        /** @var array<string, array<string, mixed>> $additionalColumns */
        $additionalColumns = &$relationDefs[RelationParam::ADDITIONAL_COLUMNS];

        foreach ($additionalColumns as &$columnDefs) {
            $columnDefs[AttributeParam::TYPE] ??= AttributeType::VARCHAR;

            if (
                $columnDefs[AttributeParam::TYPE] === AttributeType::VARCHAR &&
                !isset($columnDefs[AttributeParam::LEN])
            ) {
                $columnDefs[AttributeParam::LEN] = self::DEFAULT_VARCHAR_LENGTH;
            }
        }

        $relationDefs[RelationParam::ADDITIONAL_COLUMNS] = $additionalColumns;
    }
}
