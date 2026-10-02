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

use Espo\Core\Container;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\ORM\EntityManager;
use Espo\Core\Utils\Config;

class AfterUpgrade
{
    public function run(Container $container): void
    {
        $this->updateEventMetadata($container->get('metadata'), $container->get('fileManager'));
        $this->updateTheme($container->get('entityManager'), $container->get('config'));
        $this->updateKbArticles($container->get('entityManager'));
    }

    private function updateEventMetadata(Metadata $metadata, FileManager $fileManager): void
    {
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

    private function updateTheme(EntityManager $entityManager, Config $config): void
    {
        $themeList = [
            'EspoVertical',
            'HazyblueVertical',
            'VioletVertical',
            'SakuraVertical',
            'DarkVertical',
        ];

        $theme = $config->get('theme');
        $navbar = 'top';

        if (in_array($theme, $themeList)) {
            $theme = substr($theme, 0, -8);
            $navbar = 'side';
        }

        $config->set('theme', $theme);
        $config->set('themeParams', (object) ['navbar' => $navbar]);
        $config->save();

        $userList = $entityManager->getRDBRepository(\Espo\Entities\User::ENTITY_TYPE)
            ->where([
                'type' => ['regular', 'admin']
            ])
            ->find();

        foreach ($userList as $user) {
            $preferences = $entityManager->getEntityById(\Espo\Entities\Preferences::ENTITY_TYPE, $user->getId());

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

            $entityManager->saveEntity($preferences);
        }

        $portalList = $entityManager->getRDBRepository(\Espo\Entities\Portal::ENTITY_TYPE)
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

            $entityManager->saveEntity($portal);
        }
    }

     private function updateKbArticles(EntityManager $entityManager): void
     {
         $query = $entityManager
             ->getQueryBuilder()
             ->update()
             ->in(\Espo\Modules\Crm\Entities\KnowledgeBaseArticle::ENTITY_TYPE)
             ->where(['type' => null])
             ->set(['type' => 'Article'])
             ->build();

         $entityManager->getQueryExecutor()->execute($query);
     }
}
