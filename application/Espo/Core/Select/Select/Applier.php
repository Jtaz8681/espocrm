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

namespace Espo\Core\Select\Select;

use Espo\Core\Select\SearchParams;
use Espo\Core\Utils\FieldUtil;

use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\SelectBuilder;

class Applier
{
    /** @var string[] */
    private $aclAttributeList = [
        'assignedUserId',
        'createdById',
    ];

    /** @var string[] */
    private $aclPortalAttributeList = [
        'assignedUserId',
        'createdById',
        'contactId',
        'accountId',
    ];

    public function __construct(
        private string $entityType,
        private User $user,
        private FieldUtil $fieldUtil,
        private MetadataProvider $metadataProvider
    ) {}

    public function apply(SelectBuilder $queryBuilder, SearchParams $searchParams): void
    {
        $attributeList = $this->getSelectAttributeList($searchParams);

        if ($attributeList) {
            $queryBuilder->select(
                $this->prepareAttributeList($attributeList, $searchParams)
            );
        }
    }

    /**
     * @param string[] $attributeList
     * @return array<int, array{string, string}|string>
     */
    private function prepareAttributeList(array $attributeList, SearchParams $searchParams): array
    {
        $limit = $searchParams->getMaxTextAttributeLength();

        if ($limit === null) {
            return $attributeList;
        }

        $resultList = [];

        foreach ($attributeList as $item) {
            if (
                $this->metadataProvider->hasAttribute($this->entityType, $item) &&
                $this->metadataProvider->getAttributeType($this->entityType, $item) === Entity::TEXT &&
                !$this->metadataProvider->isAttributeNotStorable($this->entityType, $item)
            ) {
                $resultList[] = [
                    "LEFT:($item, $limit)",
                    $item
                ];

                continue;
            }

            $resultList[] = $item;
        }

        return $resultList;
    }

    /**
     * @return ?string[]
     */
    private function getSelectAttributeList(SearchParams $searchParams): ?array
    {
        $passedAttributeList = $searchParams->getSelect();

        if (!$passedAttributeList) {
            return null;
        }

        if ($passedAttributeList === ['*']) {
            return ['*'];
        }

        $attributeList = [];

        if (!in_array(Attribute::ID, $passedAttributeList)) {
            $attributeList[] = Attribute::ID;
        }

        foreach ($this->getAclAttributeList() as $attribute) {
            if (in_array($attribute, $passedAttributeList)) {
                continue;
            }

            if (!$this->metadataProvider->hasAttribute($this->entityType, $attribute)) {
                continue;
            }

            $attributeList[] = $attribute;
        }

        foreach ($passedAttributeList as $attribute) {
            if (in_array($attribute, $attributeList)) {
                continue;
            }

            if (!$this->metadataProvider->hasAttribute($this->entityType, $attribute)) {
                continue;
            }

            $attributeList[] = $attribute;
        }

        $orderByField = $searchParams->getOrderBy() ?? $this->metadataProvider->getDefaultOrderBy($this->entityType);

        if ($orderByField) {
            $sortByAttributeList = $this->fieldUtil->getAttributeList($this->entityType, $orderByField);

            foreach ($sortByAttributeList as $attribute) {
                if (in_array($attribute, $attributeList)) {
                    continue;
                }

                if (!$this->metadataProvider->hasAttribute($this->entityType, $attribute)) {
                    continue;
                }

                $attributeList[] = $attribute;
            }
        }

        $selectAttributesDependencyMap =
            $this->metadataProvider->getSelectAttributesDependencyMap($this->entityType) ?? [];

        foreach ($selectAttributesDependencyMap as $attribute => $dependantAttributeList) {
            if (!in_array($attribute, $attributeList)) {
                continue;
            }

            foreach ($dependantAttributeList as $dependantAttribute) {
                if (in_array($dependantAttribute, $attributeList)) {
                    continue;
                }

                $attributeList[] = $dependantAttribute;
            }
        }

        return $attributeList;
    }

    /**
     * @return string[]
     */
    private function getAclAttributeList(): array
    {
        if ($this->user->isPortal()) {
            return
                $this->metadataProvider->getAclPortalAttributeList($this->entityType) ??
                $this->aclPortalAttributeList;
        }

        return
            $this->metadataProvider->getAclAttributeList($this->entityType) ??
            $this->aclAttributeList;
    }
}
