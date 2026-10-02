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

use Espo\Core\DataManager;
use Espo\Core\Exceptions\Error;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\Modules\Crm\Entities\Task;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\ORM\Defs\Params\RelationParam;
use Espo\ORM\Type\RelationType;
use Espo\Tools\EntityManager\Hook\UpdateHook;
use Espo\Tools\EntityManager\Params;

/**
 * @noinspection PhpUnused
 */
class CollaboratorsUpdateHook implements UpdateHook
{
    private const PARAM = 'collaborators';
    private const FIELD = Field::COLLABORATORS;
    private const RELATION_NAME = 'entityCollaborator';

    private const DEFAULT_MAX_COUNT = 30;

    /**
     * @var string[]
     */
    private array $enabledByDefaultEntityTypeList = [
        CaseObj::ENTITY_TYPE,
        Task::ENTITY_TYPE,
    ];

    public function __construct(
        private Metadata $metadata,
        private Log $log,
        private DataManager $dataManager,
    ) {}

    /**
     * @throws Error
     */
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
        if ($this->metadata->get("entityDefs.$entityType.links." . self::FIELD . ".isCustom")) {
            $this->log->warning("Cannot enable collaborators for $entityType as the link already exists.");

            return;
        }

        if ($this->isEnabledByDefault($entityType)) {
            $this->addEnabledByDefault($entityType);
        } else {
            $this->addInternal($entityType);
        }

        $this->metadata->save();
        $this->dataManager->rebuild([$entityType]);
    }

    private function remove(string $entityType): void
    {
        $field = self::FIELD;

        if (
            $this->metadata->get("entityDefs.$entityType.links.$field.isCustom") &&
            $this->metadata->get("entityDefs.$entityType.links.$field.relationName") !== self::RELATION_NAME
        ) {
            return;
        }

        $this->metadata->delete('entityDefs', $entityType, [
            'fields.' . self::FIELD,
            'links.' . self::FIELD,
        ]);

        $this->metadata->delete('entityAcl', $entityType, [
            'links.' . self::FIELD,
        ]);

        $this->metadata->save();

        // Must be after metadata is saved.
        if ($this->isEnabledByDefault($entityType)) {
            $this->metadata->set('entityDefs', $entityType, [
                'fields' => [
                    self::FIELD => [
                        FieldParam::DISABLED => true,
                    ],
                ],
                'links' => [
                    self::FIELD => [
                        RelationParam::DISABLED => true,
                    ],
                ],
            ]);

            $this->metadata->save();
        }
    }

    private function addInternal(string $entityType): void
    {
        $this->metadata->set('entityDefs', $entityType, [
            'fields' => [
                self::FIELD => [
                    'type' => FieldType::LINK_MULTIPLE,
                    'view' => 'views/fields/collaborators',
                    'maxCount' => self::DEFAULT_MAX_COUNT,
                    'fieldManagerParamList' => [
                        'readOnly',
                        'readOnlyAfterCreate',
                        'audited',
                        'autocompleteOnEmpty',
                        'maxCount',
                        'inlineEditDisabled',
                        'tooltipText',
                    ]
                ],
            ],
            'links' => [
                self::FIELD => [
                    'type' => RelationType::HAS_MANY,
                    'entity' => User::ENTITY_TYPE,
                    RelationParam::RELATION_NAME => self::RELATION_NAME,
                    'layoutRelationshipsDisabled' => true,
                    RelationParam::READ_ONLY => true,
                ],
            ],
        ]);

        $this->metadata->set('entityAcl', $entityType, [
            'links' => [
                self::FIELD => [
                    'readOnly' => true,
                ],
            ],
        ]);
    }

    private function addEnabledByDefault(string $entityType): void
    {
        $this->metadata->delete('entityDefs', $entityType, [
            'fields.' . self::FIELD,
            'links.' . self::FIELD,
        ]);
    }

    /**
     * @param string $entityType
     * @return bool
     */
    private function isEnabledByDefault(string $entityType): bool
    {
        return in_array($entityType, $this->enabledByDefaultEntityTypeList);
    }
}
