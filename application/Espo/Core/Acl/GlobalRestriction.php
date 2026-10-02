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

namespace Espo\Core\Acl;

use Espo\Core\Utils\Config\SystemConfig;
use Espo\Core\Utils\DataCache;
use Espo\Core\Utils\FieldUtil;
use Espo\Core\Utils\Metadata;

use stdClass;

/**
 * Lists of restricted fields can be obtained from here. Restricted fields
 * are specified in metadata > entityAcl.
 */
class GlobalRestriction
{
    /** Totally forbidden. */
    public const TYPE_FORBIDDEN = 'forbidden';
    /** Reading forbidden, writing allowed. */
    public const TYPE_INTERNAL = 'internal';
    /** Forbidden for non-admin users. */
    public const TYPE_ONLY_ADMIN = 'onlyAdmin';
    /** Read-only for all users. */
    public const TYPE_READ_ONLY = 'readOnly';
    /** Read-only for non-admin users. */
    public const TYPE_NON_ADMIN_READ_ONLY = 'nonAdminReadOnly';

    /**
     * @var array<int, self::TYPE_*>
     */
    private $fieldTypeList = [
        self::TYPE_FORBIDDEN,
        self::TYPE_INTERNAL,
        self::TYPE_ONLY_ADMIN,
        self::TYPE_READ_ONLY,
        self::TYPE_NON_ADMIN_READ_ONLY,
    ];

    /**
     * @var array<int, self::TYPE_*>
     */
    private $linkTypeList = [
        self::TYPE_FORBIDDEN,
        self::TYPE_INTERNAL,
        self::TYPE_ONLY_ADMIN,
        self::TYPE_READ_ONLY,
        self::TYPE_NON_ADMIN_READ_ONLY,
    ];

    /**
     * Types that should also be taken from entityDefs.
     * @var array<int, self::TYPE_*>
     */
    private array $entityDefsTypeList = [
        self::TYPE_READ_ONLY,
    ];

    private ?stdClass $data = null;

    private string $cacheKey = 'entityAcl';

    public function __construct(
        private Metadata $metadata,
        private DataCache $dataCache,
        private FieldUtil $fieldUtil,
        SystemConfig $systemConfig,
    ) {

        $useCache = $systemConfig->useCache();

        if ($useCache && $this->dataCache->has($this->cacheKey)) {
            /** @var stdClass $cachedData */
            $cachedData = $this->dataCache->get($this->cacheKey);

            $this->data = $cachedData;

            return;
        }

        if (!$this->data) {
            $this->buildData();
        }

        if ($useCache) {
            $this->storeCacheFile();
        }
    }

    private function storeCacheFile(): void
    {
        assert($this->data !== null);

        $this->dataCache->store($this->cacheKey, $this->data);
    }

    private function buildData(): void
    {
        /** @var string[] $scopeList */
        $scopeList = array_keys($this->metadata->get(['entityDefs']) ?? []);

        $data = (object) [];

        foreach ($scopeList as $scope) {
            /** @var string[] $fieldList */
            $fieldList = array_keys($this->metadata->get(['entityDefs', $scope, 'fields']) ?? []);
            /** @var string[] $linkList */
            $linkList = array_keys($this->metadata->get(['entityDefs', $scope, 'links']) ?? []);

            $isNotEmpty = false;

            $scopeData = (object) [
                'fields' => (object) [],
                'attributes' => (object) [],
                'links' => (object) [],
            ];

            foreach ($this->fieldTypeList as $type) {
                $resultFieldList = [];
                $resultAttributeList = [];

                foreach ($fieldList as $field) {
                    $value = $this->metadata->get(['entityAcl', $scope, 'fields', $field, $type]);

                    if (!$value && in_array($type, $this->entityDefsTypeList)) {
                        $value = $this->metadata->get(['entityDefs', $scope, 'fields', $field, $type]);
                    }

                    if (
                        $type === self::TYPE_FORBIDDEN &&
                        $this->metadata->get("entityDefs.$scope.fields.$field.disabled")
                    ) {
                        $value = true;
                    }

                    if (!$value) {
                        continue;
                    }

                    $isNotEmpty = true;

                    $resultFieldList[] = $field;

                    foreach ($this->fieldUtil->getAttributeList($scope, $field) as $attribute) {
                        $resultAttributeList[] = $attribute;
                    }
                }

                $scopeData->fields->$type = $resultFieldList;
                $scopeData->attributes->$type = $resultAttributeList;
            }

            foreach ($this->linkTypeList as $type) {
                $resultLinkList = [];

                foreach ($linkList as $link) {
                    $value = $this->metadata->get(['entityAcl', $scope, 'links', $link, $type]);

                    if (!$value && in_array($type, $this->entityDefsTypeList)) {
                        $value = $this->metadata->get(['entityDefs', $scope, 'links', $link, $type]);
                    }

                    if (
                        $type === self::TYPE_FORBIDDEN &&
                        $this->metadata->get("entityDefs.$scope.links.$link.disabled")
                    ) {
                        $value = true;
                    }

                    if (!$value) {
                        continue;
                    }

                    $isNotEmpty = true;

                    $resultLinkList[] = $link;
                }

                $scopeData->links->$type = $resultLinkList;
            }

            if ($isNotEmpty) {
                $data->$scope = $scopeData;
            }
        }

        $this->data = $data;
    }

    /**
     * @param self::TYPE_* $type
     * @return string[]
     */
    public function getScopeRestrictedFieldList(string $scope, string $type): array
    {
        assert($this->data !== null);

        if (!property_exists($this->data, $scope)) {
            return [];
        }

        if (!property_exists($this->data->$scope, 'fields')) {
            return [];
        }

        if (!property_exists($this->data->$scope->fields, $type)) {
            return [];
        }

        return $this->data->$scope->fields->$type;
    }

    /**
     * @param self::TYPE_* $type
     * @return string[]
     */
    public function getScopeRestrictedAttributeList(string $scope, string $type): array
    {
        assert($this->data !== null);

        if (!property_exists($this->data, $scope)) {
            return [];
        }

        if (!property_exists($this->data->$scope, 'attributes')) {
            return [];
        }

        if (!property_exists($this->data->$scope->attributes, $type)) {
            return [];
        }

        return $this->data->$scope->attributes->$type;
    }

    /**
     * @param self::TYPE_* $type
     * @return string[]
     */
    public function getScopeRestrictedLinkList(string $scope, string $type): array
    {
        assert($this->data !== null);

        if (!property_exists($this->data, $scope)) {
            return [];
        }

        if (!property_exists($this->data->$scope, 'links')) {
            return [];
        }

        if (!property_exists($this->data->$scope->links, $type)) {
            return [];
        }

        return $this->data->$scope->links->$type;
    }
}
