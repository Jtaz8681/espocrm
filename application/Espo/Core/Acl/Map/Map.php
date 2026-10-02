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

namespace Espo\Core\Acl\Map;

use Espo\Core\Acl\Table;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\ObjectUtil;

use stdClass;
use RuntimeException;

/**
 * Provides quick access to ACL data.
 */
class Map
{
    private stdClass $data;
    private string $cacheKey;
    /** @var array<string, string[]> */
    private $forbiddenFieldsCache = [];
    /** @var array<string, string[]> */
    private $forbiddenAttributesCache;
    /** @var string[] */
    private $fieldLevelList = [
        Table::LEVEL_YES,
        Table::LEVEL_NO,
    ];

    public function __construct(
        Table $table,
        private DataBuilder $dataBuilder,
        private DataCache $dataCache,
        CacheKeyProvider $cacheKeyProvider,
        Config\SystemConfig $systemConfig,
    ) {

        $this->cacheKey = $cacheKeyProvider->get();

        if ($systemConfig->useCache() && $this->dataCache->has($this->cacheKey)) {
            /** @var stdClass $cachedData */
            $cachedData = $this->dataCache->get($this->cacheKey);

            $this->data = $cachedData;
        } else {
            $this->data = $this->dataBuilder->build($table);

            if ($systemConfig->useCache()) {
                $this->dataCache->store($this->cacheKey, $this->data);
            }
        }
    }

    /**
     * Get raw data (for front-end).
     */
    public function getData(): stdClass
    {
        return ObjectUtil::clone($this->data);
    }

    /**
     * Get a list of forbidden attributes for a scope and action.
     *
     * @param string $scope A scope.
     * @param string $action An action.
     * @param string $thresholdLevel An attribute will be treated as forbidden if the level is
     * equal to or lower than the threshold.
     * @return string[]
     */
    public function getScopeForbiddenAttributeList(
        string $scope,
        string $action = Table::ACTION_READ,
        string $thresholdLevel = Table::LEVEL_NO
    ): array {

        if (
            !in_array($thresholdLevel, $this->fieldLevelList) ||
            $thresholdLevel === Table::LEVEL_YES
        ) {
            throw new RuntimeException("Bad threshold level.");
        }

        $key = $scope . '_'. $action . '_' . $thresholdLevel;

        if (isset($this->forbiddenAttributesCache[$key])) {
            return $this->forbiddenAttributesCache[$key];
        }

        $fieldTableQuickAccess = $this->data->fieldTableQuickAccess;

        if (
            !isset($fieldTableQuickAccess->$scope) ||
            !isset($fieldTableQuickAccess->$scope->attributes) ||
            !isset($fieldTableQuickAccess->$scope->attributes->$action)
        ) {
            $this->forbiddenAttributesCache[$key] = [];

            return [];
        }

        $levelList = [];

        foreach ($this->fieldLevelList as $level) {
            if (
                array_search($level, $this->fieldLevelList) >=
                array_search($thresholdLevel, $this->fieldLevelList)
            ) {
                $levelList[] = $level;
            }
        }

        $attributeList = [];

        foreach ($levelList as $level) {
            if (!isset($fieldTableQuickAccess->$scope->attributes->$action->$level)) {
                continue;
            }

            foreach ($fieldTableQuickAccess->$scope->attributes->$action->$level as $attribute) {
                if (in_array($attribute, $attributeList)) {
                    continue;
                }

                $attributeList[] = $attribute;
            }
        }

        $this->forbiddenAttributesCache[$key] = $attributeList;

        return $attributeList;
    }

    /**
     * Get a list of forbidden fields for a scope and action.
     *
     * @param string $scope A scope.
     * @param string $action An action.
     * @param string $thresholdLevel An attribute will be treated as forbidden if the level is
     * equal to or lower than the threshold.
     * @return string[]
     */
    public function getScopeForbiddenFieldList(
        string $scope,
        string $action = Table::ACTION_READ,
        string $thresholdLevel = Table::LEVEL_NO
    ): array {

        if (
            !in_array($thresholdLevel, $this->fieldLevelList) ||
            $thresholdLevel === Table::LEVEL_YES
        ) {
            throw new RuntimeException("Bad threshold level.");
        }

        $key = $scope . '_'. $action . '_' . $thresholdLevel;

        if (isset($this->forbiddenFieldsCache[$key])) {
            return $this->forbiddenFieldsCache[$key];
        }

        $fieldTableQuickAccess = $this->data->fieldTableQuickAccess;

        if (
            !isset($fieldTableQuickAccess->$scope) ||
            !isset($fieldTableQuickAccess->$scope->fields) ||
            !isset($fieldTableQuickAccess->$scope->fields->$action)
        ) {
            $this->forbiddenFieldsCache[$key] = [];

            return [];
        }

        $levelList = [];

        foreach ($this->fieldLevelList as $level) {
            if (
                array_search($level, $this->fieldLevelList) >=
                array_search($thresholdLevel, $this->fieldLevelList)
            ) {
                $levelList[] = $level;
            }
        }

        $fieldList = [];

        foreach ($levelList as $level) {
            if (!isset($fieldTableQuickAccess->$scope->fields->$action->$level)) {
                continue;
            }

            foreach ($fieldTableQuickAccess->$scope->fields->$action->$level as $field) {
                if (in_array($field, $fieldList)) {
                    continue;
                }

                $fieldList[] = $field;
            }
        }

        $this->forbiddenFieldsCache[$key] = $fieldList;

        return $fieldList;
    }
}
