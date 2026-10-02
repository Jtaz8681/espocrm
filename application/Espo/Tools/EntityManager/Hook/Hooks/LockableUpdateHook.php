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
use Espo\Modules\Crm\Entities\Account;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\Tools\EntityManager\Hook\UpdateHook;
use Espo\Tools\EntityManager\Params;

/**
 * @noinspection PhpUnused
 */
class LockableUpdateHook implements UpdateHook
{
    private const string PARAM = 'lockable';
    private const string FIELD = Field::IS_LOCKED;

    /** @var string[] */
    private array $enabledByDefaultEntityTypeList = [
        Account::ENTITY_TYPE,
    ];

    public function __construct(
        private Metadata $metadata,
        private Log $log,
        private DataManager $dataManager,
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
        if ($this->metadata->get("entityDefs.$entityType.fields." . self::FIELD . ".isCustom")) {
            $this->log->warning("Cannot enable lockable for $entityType as the field already exists.");

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

    private function addEnabledByDefault(string $entityType): void
    {
        $this->metadata->delete('entityDefs', $entityType, [
            'fields.' . self::FIELD,
        ]);
    }

    private function addInternal(string $entityType): void
    {
        $this->metadata->set('entityDefs', $entityType, [
            'fields' => [
                self::FIELD => [
                    FieldParam::TYPE => FieldType::BOOL,
                    FieldParam::READ_ONLY => true,
                    'audited' => true,
                    'fieldManagerParamList' => [
                        'audited',
                        'tooltipText',
                    ],
                    'layoutAvailabilityList' => [
                        'filters',
                        'list',
                    ],
                ],
            ]
        ]);

    }

    private function remove(string $entityType): void
    {
        $this->metadata->delete('entityDefs', $entityType, [
            'fields.' . self::FIELD,
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
            ]);

            $this->metadata->save();
        }
    }

    private function isEnabledByDefault(string $entityType): bool
    {
        return in_array($entityType, $this->enabledByDefaultEntityTypeList);
    }
}
