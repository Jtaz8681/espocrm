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

namespace Espo\Core\Upgrades\Migrations\V8_0;

use Espo\Core\Container;
use Espo\Core\Name\Field;
use Espo\Core\Upgrades\Migration\Script;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Role;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\UpdateBuilder;

class AfterUpgrade implements Script
{
    public function __construct(
        private Container $container
    ) {}

    public function run(): void
    {
        $this->updateRoles(
            $this->container->getByClass(EntityManager::class)
        );

        $this->updateMetadata(
            $this->container->getByClass(Metadata::class)
        );
    }

    private function updateRoles(EntityManager $entityManager): void
    {
        $query = UpdateBuilder::create()
            ->in(Role::ENTITY_TYPE)
            ->set(['messagePermission' => Expression::column('assignmentPermission')])
            ->build();

        $entityManager->getQueryExecutor()->execute($query);
    }

    private function updateMetadata(Metadata $metadata): void
    {
        $defs = $metadata->get(['scopes']);

        foreach ($defs as $entityType => $item) {
            $isCustom = $item['isCustom'] ?? false;
            $type = $item['type'] ?? false;

            if (!$isCustom) {
                continue;
            }

            if ($type === 'Event') {
                $metadata->set('entityDefs', $entityType, [
                    'fields' => [
                        'dateEnd' => [
                            'suppressValidationList' => ['required'],
                        ],
                    ]
                ]);

                $metadata->save();

                continue;
            }

            if (
                !in_array($type, [
                    'BasePlus',
                    'Base',
                    'Company',
                    'Person',
                ])
            ) {
                continue;
            }

            $recordDefs = $metadata->getCustom('recordDefs', $entityType) ?? (object) [];
            $scopes = $metadata->getCustom('scopes', $entityType) ?? (object) [];

            $recordDefs->duplicateWhereBuilderClassName = "Espo\\Classes\\DuplicateWhereBuilders\\General";

            $scopes->duplicateCheckFieldList = [];

            if ($type === 'Company' || $type === 'Person') {
                $scopes->duplicateCheckFieldList = [Field::NAME, 'emailAddress'];
            }

            $metadata->saveCustom('recordDefs', $entityType, $recordDefs);
            $metadata->saveCustom('scopes', $entityType, $scopes);
        }
    }
}
