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

namespace Espo\Tools\EntityManager\Hook\Hooks;

use Espo\Classes\Acl\Common\Pipeline\PipelineLinkChecker;
use Espo\Classes\Acl\Common\Pipeline\PipelineStageLinkChecker;
use Espo\Classes\FieldValidators\Common\PipelineStage\Valid;
use Espo\Core\DataManager;
use Espo\Core\Exceptions\Error;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Pipeline;
use Espo\Entities\PipelineStage;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\ORM\Type\RelationType;
use Espo\Tools\EntityManager\Hook\UpdateHook;
use Espo\Tools\EntityManager\Params;
use Espo\Tools\LayoutManager\LayoutCustomizer;
use Espo\Tools\LayoutManager\LayoutName;

/**
 * @noinspection PhpUnused
 */
class PipelinesUpdateHook implements UpdateHook
{
    private const string PARAM = 'pipelines';

    public function __construct(
        private Metadata $metadata,
        private DataManager $dataManager,
        private LayoutCustomizer $layoutCustomizer,
        private Log $log,
    ) {}

    public function process(Params $params, Params $previousParams): void
    {
        if ($params->get(self::PARAM) && !$previousParams->get(self::PARAM)) {
            $this->add($params->getName());
        } else if (!$params->get(self::PARAM) && $previousParams->get(self::PARAM)) {
            $this->remove($params->getName());
        }
    }

    /**
     * @throws Error
     */
    private function add(string $entityType): void
    {
        if ($this->metadata->get("entityDefs.$entityType.links." . Field::PIPELINE . ".isCustom")) {
            $this->log->warning("Cannot enable pipelines for $entityType as the link already exists.");

            return;
        }

        $this->metadata->set('entityDefs', $entityType, [
            'fields' => [
                Field::PIPELINE => [
                    FieldParam::TYPE => FieldType::LINK,
                    FieldParam::REQUIRED => true,
                    'audited' => true,
                    'inlineEditDisabled' => true,
                    'fieldManagerParamList' => [
                        FieldParam::READ_ONLY_AFTER_CREATE,
                        'audited',
                        'tooltipText',
                    ],
                    'view' => 'views/fields/extra/pipeline',
                ],
                Field::PIPELINE_STAGE => [
                    FieldParam::TYPE => FieldType::LINK,
                    FieldParam::REQUIRED => true,
                    'audited' => true,
                    'validationDependsOnFieldList' => [
                        Field::PIPELINE,
                    ],
                    'validatorClassNameList' => [
                        Valid::class,
                    ],
                    'fieldManagerParamList' => [
                        'audited',
                        'tooltipText',
                        'inlineEditDisabled',
                    ],
                    'view' => 'views/fields/extra/pipeline-stage',
                    'dynamicLogicConditionTypeList' => [
                        'isEmpty',
                        'isNotEmpty',
                    ]
                ],
            ],
            'links' => [
                Field::PIPELINE => [
                    'type' => RelationType::BELONGS_TO,
                    'entity' => Pipeline::ENTITY_TYPE,
                ],
                Field::PIPELINE_STAGE => [
                    'type' => RelationType::BELONGS_TO,
                    'entity' => PipelineStage::ENTITY_TYPE,
                ],
            ],
        ]);

        $this->metadata->set('aclDefs', $entityType, [
            'linkCheckerClassNameMap' => [
                Field::PIPELINE => PipelineLinkChecker::class,
                Field::PIPELINE_STAGE => PipelineStageLinkChecker::class,
            ],
        ]);

        $this->metadata->set('selectDefs', $entityType, [
            'selectAttributesDependencyMap' => [
                Field::PIPELINE_STAGE . 'Id' => [
                    Field::PIPELINE . 'Id',
                ]
            ]
        ]);

        $this->metadata->save();
        $this->dataManager->rebuild([$entityType]);

        $this->layoutCustomizer->addDetailField($entityType, Field::PIPELINE, LayoutName::DETAIL);
        $this->layoutCustomizer->addDetailField($entityType, Field::PIPELINE_STAGE, LayoutName::DETAIL);

        $this->layoutCustomizer->addDetailField($entityType, Field::PIPELINE, LayoutName::DETAIL_SMALL);
        $this->layoutCustomizer->addDetailField($entityType, Field::PIPELINE_STAGE, LayoutName::DETAIL_SMALL);
    }

    /**
     * @throws Error
     */
    private function remove(string $entityType): void
    {
        $field = Field::PIPELINE;

        if (
            $this->metadata->get("entityDefs.$entityType.links.$field.isCustom") &&
            $this->metadata->get("entityDefs.$entityType.links.$field.entityType") !== Pipeline::ENTITY_TYPE
        ) {
            return;
        }

        $this->metadata->delete('entityDefs', $entityType, [
            'fields.' . Field::PIPELINE,
            'fields.' . Field::PIPELINE_STAGE,
            'links.' . Field::PIPELINE,
            'links.' . Field::PIPELINE_STAGE,
        ]);

        $this->metadata->delete('entityAcl', $entityType, [
            'links.' . Field::PIPELINE,
            'links.' . Field::PIPELINE_STAGE,
        ]);

        $this->metadata->delete('aclDefs', $entityType, [
            'linkCheckerClassNameMap.' . Field::PIPELINE,
            'linkCheckerClassNameMap.' . Field::PIPELINE_STAGE,
        ]);

        $this->metadata->delete('selectDefs', $entityType, [
            'selectAttributesDependencyMap.' . Field::PIPELINE_STAGE . 'Id',
        ]);

        $this->metadata->save();
        $this->dataManager->rebuild([$entityType]);

        $this->layoutCustomizer->removeInDetail($entityType, Field::PIPELINE, LayoutName::DETAIL);
        $this->layoutCustomizer->removeInDetail($entityType, Field::PIPELINE_STAGE, LayoutName::DETAIL);

        $this->layoutCustomizer->removeInDetail($entityType, Field::PIPELINE, LayoutName::DETAIL_SMALL);
        $this->layoutCustomizer->removeInDetail($entityType, Field::PIPELINE_STAGE, LayoutName::DETAIL_SMALL);
    }
}
