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

use Espo\Core\Name\Field;
use Espo\Core\Templates\Entities\BasePlus;
use Espo\Core\Templates\Entities\Company;
use Espo\Core\Templates\Entities\Person;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Modules\Crm\Entities\Task;
use Espo\Tools\EntityManager\Hook\CreateHook;
use Espo\Tools\EntityManager\Params;

class PlusCreateHook implements CreateHook
{
    public function __construct(
        private Config $config,
        private Metadata $metadata
    ) {}

    public function process(Params $params): void
    {
        if (
            !in_array($params->getType(), [
                BasePlus::TEMPLATE_TYPE,
                Company::TEMPLATE_TYPE,
                Person::TEMPLATE_TYPE,
            ])
        ) {
            return;
        }

        $name = $params->getName();

        $activitiesEntityTypeList = $this->config->get('activitiesEntityList', []);
        $historyEntityTypeList = $this->config->get('historyEntityList', []);

        $entityTypeList = array_merge($activitiesEntityTypeList, $historyEntityTypeList);
        $entityTypeList[] = Task::ENTITY_TYPE;
        $entityTypeList = array_unique($entityTypeList);

        foreach ($entityTypeList as $entityType) {
            if (!$this->metadata->get(['entityDefs', $entityType, 'fields', Field::PARENT, 'entityList'])) {
                continue;
            }

            $list = $this->metadata->get(['entityDefs', $entityType, 'fields', Field::PARENT, 'entityList'], []);

            if (!in_array($name, $list)) {
                $list[] = $name;

                $data = [
                    'fields' => [
                        Field::PARENT => ['entityList' => $list]
                    ]
                ];

                $this->metadata->set('entityDefs', $entityType, $data);
            }
        }

        $this->metadata->save();
    }
}
