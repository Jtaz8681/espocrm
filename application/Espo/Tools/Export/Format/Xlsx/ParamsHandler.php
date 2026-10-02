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

namespace Espo\Tools\Export\Format\Xlsx;

use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Entity;
use Espo\Tools\Export\Params;
use Espo\Tools\Export\Processor;
use Espo\Tools\Export\ProcessorParamsHandler;

class ParamsHandler implements ProcessorParamsHandler
{
    public function __construct(
        private Metadata $metadata
    ) {}

    public function handle(Params $params, Processor\Params $processorParams): Processor\Params
    {
        $fieldList = $processorParams->getFieldList();

        if ($fieldList === null) {
            return $processorParams;
        }

        $fieldList = $this->filterFieldList($params->getEntityType(), $fieldList, $params->allFields());

        $attributeList = $processorParams->getAttributeList();

        $this->addAdditionalAttributes($params->getEntityType(), $attributeList, $fieldList);

        return $processorParams
            ->withAttributeList($attributeList)
            ->withFieldList($fieldList);
    }

    /**
     * @param string[] $fieldList
     * @return string[]
     */
    private function filterFieldList(string $entityType, array $fieldList, bool $exportAllFields): array
    {
        if ($exportAllFields) {
            foreach ($fieldList as $i => $field) {
                $type = $this->metadata->get(['entityDefs', $entityType, 'fields', $field, 'type']);

                if (in_array($type, [FieldType::LINK_MULTIPLE, FieldType::ATTACHMENT_MULTIPLE])) {
                    unset($fieldList[$i]);
                }
            }
        }

        return array_values($fieldList);
    }

    /**
     * @param string[] $attributeList
     * @param string[] $fieldList
     */
    private function addAdditionalAttributes(string $entityType, array &$attributeList, array $fieldList): void
    {
        $linkList = [];

        if (!in_array('id', $attributeList)) {
            $attributeList[] = 'id';
        }

        $linkDefs = $this->metadata->get(['entityDefs', $entityType, 'links']) ?? [];

        foreach ($linkDefs as $link => $defs) {
            $linkType = $defs['type'] ?? null;

            if (!$linkType) {
                continue;
            }

            if ($linkType === Entity::BELONGS_TO_PARENT) {
                $linkList[] = $link;

                continue;
            }

            if ($linkType === Entity::BELONGS_TO && !empty($defs[RelationParam::NO_JOIN])) {
                if ($this->metadata->get(['entityDefs', $entityType, 'fields', $link])) {
                    $linkList[] = $link;
                }
            }
        }

        foreach ($linkList as $item) {
            if (in_array($item, $fieldList) && !in_array($item . 'Name', $attributeList)) {
                $attributeList[] = $item . 'Name';
            }
        }

        foreach ($fieldList as $field) {
            $type = $this->metadata->get(['entityDefs', $entityType, 'fields', $field, 'type']);

            if ($type === FieldType::CURRENCY_CONVERTED) {
                if (!in_array($field, $attributeList)) {
                    $attributeList[] = $field;
                }
            }
        }
    }
}
