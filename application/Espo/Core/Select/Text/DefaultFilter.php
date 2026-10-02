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

namespace Espo\Core\Select\Text;

use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Select\Text\Filter\Data;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Query\Part\Where\OrGroup;
use Espo\ORM\Query\Part\Where\OrGroupBuilder;
use Espo\ORM\Query\Part\Where\Comparison as Cmp;
use Espo\ORM\Query\Part\Expression as Expr;

use Espo\ORM\Entity;
use RuntimeException;

class DefaultFilter implements Filter
{
    public function __construct(
        private string $entityType,
        private MetadataProvider $metadataProvider,
        private ConfigProvider $config,
    ) {}

    public function apply(SelectBuilder $queryBuilder, Data $data): void
    {
        $orGroupBuilder = OrGroup::createBuilder();

        foreach ($data->getAttributeList() as $attribute) {
            $this->applyAttribute($queryBuilder, $orGroupBuilder, $attribute, $data);
        }

        if ($data->getFullTextSearchWhereItem()) {
            $orGroupBuilder->add(
                $data->getFullTextSearchWhereItem()
            );
        }

        $orGroup = $orGroupBuilder->build();

        if ($orGroup->getItemCount() === 0) {
            $queryBuilder->where([Attribute::ID => null]);

            return;
        }

        $queryBuilder->where($orGroup);
    }

    /**
     * @todo AttributeFilterFactory.
     */
    private function applyAttribute(
        SelectBuilder $queryBuilder,
        OrGroupBuilder $orGroupBuilder,
        string $attribute,
        Data $data
    ): void {

        $filter = $data->getFilter();
        $skipWildcards = $data->skipWildcards();

        $attributeType = $this->getAttributeTypeAndApplyJoin($queryBuilder, $attribute);

        if ($attributeType === Entity::INT) {
            if (is_numeric($filter)) {
                $orGroupBuilder->add(
                    Cmp::equal(
                        Expr::column($attribute),
                        intval($filter)
                    )
                );
            }

            return;
        }

        if (
            !str_contains($attribute, '.') &&
            $this->metadataProvider->getFieldType($this->entityType, $attribute) === FieldType::EMAIL &&
            str_contains($filter, ' ')
        ) {
            return;
        }

        if (
            !str_contains($attribute, '.') &&
            $this->metadataProvider->getFieldType($this->entityType, $attribute) === FieldType::PHONE
        ) {
            if (!preg_match("#[0-9()\-+% ]+$#", $filter)) {
                return;
            }

            $hasPlus = str_starts_with($filter, '+');

            if ($this->config->usePhoneNumberNumericSearch()) {
                $attribute = $attribute . 'Numeric';

                $filter = preg_replace('/[^0-9%]/', '', $filter);
            }

            if (!$filter) {
                return;
            }

            if (
                $this->config->usePhoneNumberNumericSearch() &&
                $this->config->isPhoneNumberInternational() &&
                !$skipWildcards
            ) {
                $expression = $filter . '%';

                $orGroupBuilder->add(
                    Cmp::like(Expr::column($attribute), $expression)
                );

                if (!$hasPlus) {
                    $preferredCodes = $this->config->getPreferredPhoneNumberCountryCodes();

                    foreach ($preferredCodes as $code) {
                        $orGroupBuilder->add(
                            Cmp::like(Expr::column($attribute), $code . $expression)
                        );
                    }
                }

                return;
            }
        }

        $expression = $filter;

        if (!$skipWildcards) {
            $expression = $this->checkWhetherToUseContains($attribute, $filter, $attributeType) ?
                '%' . $filter . '%' :
                $filter . '%';
        }

        $expression = addslashes($expression);

        $orGroupBuilder->add(
            Cmp::like(
                Expr::column($attribute),
                $expression
            )
        );
    }

    private function getAttributeTypeAndApplyJoin(SelectBuilder $queryBuilder, string $attribute): string
    {
        if (str_contains($attribute, '.')) {
            [$link, $foreignField] = explode('.', $attribute);

            $foreignEntityType = $this->metadataProvider->getRelationEntityType($this->entityType, $link);

            if (!$foreignEntityType) {
                throw new RuntimeException("Bad relation in text filter field '$attribute'.");
            }

            if ($this->metadataProvider->getRelationType($this->entityType, $link) === Entity::HAS_MANY) {
                $queryBuilder->distinct();
            }

            $queryBuilder->leftJoin($link);

            return $this->metadataProvider->getAttributeType($foreignEntityType, $foreignField);
        }

        $attributeType = $this->metadataProvider->getAttributeType($this->entityType, $attribute);

        if ($attributeType === Entity::FOREIGN) {
            $link = $this->metadataProvider->getAttributeRelationParam($this->entityType, $attribute);

            if ($link) {
                $queryBuilder->leftJoin($link);
            }
        }

        return $attributeType;
    }

    private function checkWhetherToUseContains(string $attribute, string $filter, string $attributeType): bool
    {
        if (mb_strlen($filter) < $this->config->getMinLengthForContentSearch()) {
            return false;
        }

        if ($attributeType === Entity::TEXT) {
            return true;
        }

        if (
            in_array(
                $attribute,
                $this->metadataProvider->getUseContainsAttributeList($this->entityType)
            )
        ) {
            return true;
        }

        if (
            $attributeType === Entity::VARCHAR &&
            $this->config->useContainsForVarchar()
        ) {
            return true;
        }

        return false;
    }
}
