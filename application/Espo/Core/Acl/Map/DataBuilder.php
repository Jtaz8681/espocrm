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
use Espo\Core\Utils\FieldUtil;

use stdClass;

class DataBuilder
{
    /** @var string[] */
    private $actionList = [
        Table::ACTION_READ,
        Table::ACTION_STREAM,
        Table::ACTION_EDIT,
        Table::ACTION_DELETE,
        Table::ACTION_CREATE,
    ];
    /** @var string[] */
    private $fieldActionList = [
        Table::ACTION_READ,
        Table::ACTION_EDIT,
    ];
    /** @var string[] */
    private $fieldLevelList = [
        Table::LEVEL_YES,
        Table::LEVEL_NO,
    ];

    public function __construct(private MetadataProvider $metadataProvider, private FieldUtil $fieldUtil)
    {}

    /**
     * @return stdClass&object{table: stdClass, fieldTable: stdClass}
     */
    public function build(Table $table): stdClass
    {
        $data = (object) [
            'table' => (object) [],
            'fieldTable' => (object) [],
        ];

        foreach ($this->metadataProvider->getScopeList() as $scope) {
            $data->table->$scope = $this->getScopeRawData($table, $scope);

            $fieldData = $this->getScopeFieldData($table, $scope);

            if ($fieldData !== null) {
                $data->fieldTable->$scope = $fieldData;
            }
        }

        foreach ($this->metadataProvider->getPermissionList() as $permission) {
            $data->{$permission . 'Permission'} = $table->getPermissionLevel($permission);
        }

        $data->fieldTableQuickAccess = $this->buildFieldTableQuickAccess($data->fieldTable);

        return $data;
    }

    /**
     * @return bool|stdClass
     */
    private function getScopeRawData(Table $table, string $scope)
    {
        $data = $table->getScopeData($scope);

        if ($data->isBoolean()) {
            return $data->isTrue();
        }

        $rawData = (object) [];

        foreach ($this->actionList as $action) {
            $rawData->$action = $data->get($action);
        }

        return $rawData;
    }

    private function getScopeFieldData(Table $table, string $scope): ?stdClass
    {
        if (!$this->metadataProvider->isScopeEntity($scope)) {
            return null;
        }

        $fieldList = $this->metadataProvider->getScopeFieldList($scope);

        $rawData = (object) [];

        foreach ($fieldList as $field) {
            $data = $table->getFieldData($scope, $field);

            if (
                $data->getRead() === Table::LEVEL_YES &&
                $data->getEdit() === Table::LEVEL_YES
            ) {
                continue;
            }

            $rawData->$field = (object) [
                Table::ACTION_READ => $data->getRead(),
                Table::ACTION_EDIT => $data->getEdit(),
            ];
        }

        return $rawData;
    }

    protected function buildFieldTableQuickAccess(stdClass $fieldTable): stdClass
    {
        $quickAccess = (object) [];

        foreach (get_object_vars($fieldTable) as $scope => $scopeData) {
            $quickAccess->$scope = $this->buildFieldTableQuickAccessScope($scope, $scopeData);
        }

        return $quickAccess;
    }

    private function buildFieldTableQuickAccessScope(string $scope, stdClass $data): stdClass
    {
        $quickAccess = (object) [
            'attributes' => (object) [],
            'fields' => (object) [],
        ];

        foreach ($this->fieldActionList as $action) {
            $quickAccess->attributes->$action = (object) [];
            $quickAccess->fields->$action = (object) [];

            foreach ($this->fieldLevelList as $level) {
                $quickAccess->attributes->$action->$level = [];
                $quickAccess->fields->$action->$level = [];
            }
        }

        foreach (get_object_vars($data) as $field => $fieldData) {
            $attributeList = $this->fieldUtil->getAttributeList($scope, $field);

            foreach ($this->fieldActionList as $action) {
                if (!isset($fieldData->$action)) {
                    continue;
                }

                foreach ($this->fieldLevelList as $level) {
                    if ($fieldData->$action === $level) {
                        $quickAccess->fields->$action->{$level}[] = $field;

                        foreach ($attributeList as $attribute) {
                            $quickAccess->attributes->$action->{$level}[] = $attribute;
                        }
                    }
                }
            }
        }

        return $quickAccess;
    }
}
