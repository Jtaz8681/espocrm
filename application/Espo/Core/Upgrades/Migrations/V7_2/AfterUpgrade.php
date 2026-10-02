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

namespace Espo\Core\Upgrades\Migrations\V7_2;

use Espo\Core\Upgrades\Migration\Script;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Portal;
use Espo\Entities\Preferences;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\KnowledgeBaseArticle;
use Espo\ORM\EntityManager;
use Espo\Core\Utils\Config;

class AfterUpgrade implements Script
{
    public function __construct(
        private Metadata $metadata,
        private EntityManager $entityManager,
        private Config $config,
        private Config\ConfigWriter $configWriter
    ) {}

    public function run(): void
    {
        $this->updateEventMetadata();
        $this->updateTheme();
        $this->updateKbArticles();
    }

    private function updateEventMetadata(): void
    {
        $metadata = $this->metadata;

        $defs = $metadata->get(['scopes']);

        $toSave = false;

        foreach ($defs as $entityType => $item) {
            $isCustom = $item['isCustom'] ?? false;
            $type = $item['type'] ?? false;

            if (!$isCustom || $type !== 'Event') {
                continue;
            }

            $toSave = true;

            $metadata->set('recordDefs', $entityType, [
                'beforeUpdateHookClassNameList' => [
                    "__APPEND__",
                    "Espo\\Classes\\RecordHooks\\Event\\BeforeUpdatePreserveDuration"
                ]
            ]);

            $metadata->set('clientDefs', $entityType, [
                'forcePatchAttributeDependencyMap' => [
                    "dateEnd" => ["dateStart"],
                    "dateEndDate" => ["dateStartDate"]
                ]
            ]);

            if ($metadata->get(['entityDefs', $entityType, 'fields', 'isAllDay'])) {
                $metadata->set('entityDefs', $entityType, [
                    'fields' => [
                        'isAllDay' => [
                            'readOnly' => false,
                        ],
                    ]
                ]);
            }
        }

        if ($toSave) {
            $metadata->save();
        }
    }

    private function updateTheme(): void
    {
        $themeList = [
            'EspoVertical',
            'HazyblueVertical',
            'VioletVertical',
            'SakuraVertical',
            'DarkVertical',
        ];

        $theme = $this->config->get('theme');
        $navbar = 'top';

        if (in_array($theme, $themeList)) {
            $theme = substr($theme, 0, -8);
            $navbar = 'side';
        }

        $this->configWriter->set('theme', $theme);
        $this->configWriter->set('themeParams', (object) ['navbar' => $navbar]);
        $this->configWriter->save();

        $userList = $this->entityManager->getRDBRepository(User::ENTITY_TYPE)
            ->where([
                'type' => ['regular', 'admin']
            ])
            ->find();

        foreach ($userList as $user) {
            $preferences = $this->entityManager->getEntityById(Preferences::ENTITY_TYPE, $user->getId());

            if (!$preferences) {
                continue;
            }

            $theme = $preferences->get('theme');
            $navbar = 'top';

            if (!$theme) {
                continue;
            }

            if (in_array($theme, $themeList)) {
                $theme = substr($theme, 0, -8);
                $navbar = 'side';
            }

            $preferences->set('theme', $theme);
            $preferences->set('themeParams', (object) ['navbar' => $navbar]);

            $this->entityManager->saveEntity($preferences);
        }

        $portalList = $this->entityManager
            ->getRDBRepository(Portal::ENTITY_TYPE)
            ->where([
                'theme!=' => null,
            ])
            ->find();

        foreach ($portalList as $portal) {
            $theme = $portal->get('theme');
            $navbar = 'top';

            if (in_array($theme, $themeList)) {
                $theme = substr($theme, 0, -8);
                $navbar = 'side';
            }

            $portal->set('theme', $theme);
            $portal->set('themeParams', (object) ['navbar' => $navbar]);

            $this->entityManager->saveEntity($portal);
        }
    }

    private function updateKbArticles(): void
    {
        $query = $this->entityManager
            ->getQueryBuilder()
            ->update()
            ->in(KnowledgeBaseArticle::ENTITY_TYPE)
            ->where(['type' => null])
            ->set(['type' => 'Article'])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($query);
    }
}
