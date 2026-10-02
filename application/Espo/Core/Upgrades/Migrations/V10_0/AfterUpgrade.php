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

namespace Espo\Core\Upgrades\Migrations\V10_0;

use Espo\Core\Upgrades\Migration\Script;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Preferences;
use Espo\Entities\User;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\ORM\EntityManager;

class AfterUpgrade implements Script
{
    public function __construct(
        private EntityManager $entityManager,
        private ConfigWriter $configWriter,
        private Metadata $metadata,
        private Config $config,
    ) {}

    public function run(): void
    {
        $this->updatePreferences();
        $this->updateConfig();
        $this->updateMetadata();
    }

    private function updatePreferences(): void
    {
        $users = $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->sth()
            ->where([
                User::ATTR_IS_ACTIVE => true,
                User::ATTR_TYPE => [
                    User::TYPE_ADMIN,
                    User::TYPE_REGULAR,
                    User::TYPE_PORTAL,
                ]
            ])
            ->find();

        foreach ($users as $user) {
            $preferences = $this->entityManager->getRepositoryByClass(Preferences::class)->getById($user->getId());

            if (!$preferences) {
                continue;
            }

            $preferences->set('notificationGrouping', true);
            $this->entityManager->saveEntity($preferences);
        }
    }

    private function updateConfig(): void
    {
        $this->configWriter->set('currencyNoJoinMode', true);

        if ($this->config->get('adminUpgradeDisabled')) {
            $this->configWriter->set('adminExtensionUpload', false);
        } else {
            $this->configWriter->set('adminExtensionUpload', true);
        }

        $this->configWriter->save();
    }

    private function updateMetadata(): void
    {
        /** @var array<string, array<string, mixed>> $clientDefs */
        $clientDefs = $this->metadata->get('clientDefs') ?? [];

        foreach ($clientDefs as $name => $defs) {
            if ($name === CaseObj::ENTITY_TYPE) {
                continue;
            }

            if ($defs['allowInternalNotes'] ?? false) {
                $this->metadata->set('streamDefs', $name, [
                    'allowInternalNotes' => true,
                ]);

                $this->metadata->delete('clientDefs', $name, ['allowInternalNotes']);
            }
        }

        $this->metadata->save();
    }
}
