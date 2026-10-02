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

namespace Espo\Tools\AdminNotifications\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\Core\Name\Field;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Entities\Extension;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\Tools\AdminNotifications\LatestReleaseDataRequester;

/**
 * Checking for new extension versions.
 */
class CheckNewExtensionVersion implements JobDataLess
{
    public function __construct(
        private Config $config,
        private ConfigWriter $configWriter,
        private EntityManager $entityManager,
        private LatestReleaseDataRequester $requester
    ) {}

    public function run(): void
    {
        if (
            !$this->config->get('adminNotifications') ||
            !$this->config->get('adminNotificationsNewExtensionVersion')
        ) {
            return;
        }

        $query = $this->entityManager
            ->getQueryBuilder()
            ->select()
            ->from(Extension::ENTITY_TYPE)
            ->select([Attribute::ID, Field::NAME, 'version', 'checkVersionUrl'])
            ->where([
                Attribute::DELETED => false,
                'isInstalled' => true,
            ])
            ->order([Field::CREATED_AT])
            ->build();

        $sth = $this->entityManager->getQueryExecutor()->execute($query);

        $latestReleases = [];

        while ($row = $sth->fetch()) {
            $url = !empty($row['checkVersionUrl']) ? $row['checkVersionUrl'] : null;

            $extensionName = $row['name'];

            $latestRelease = $this->requester->request($url, [
                'name' => $extensionName,
            ]);

            if (!empty($latestRelease) && !isset($latestRelease['error'])) {
                $latestReleases[$extensionName] = $latestRelease;
            }
        }

        $latestExtensionVersions = $this->config->get('latestExtensionVersions', []);

        $save = false;

        foreach ($latestReleases as $extensionName => $extensionData) {
            if (empty($latestExtensionVersions[$extensionName])) {
                $latestExtensionVersions[$extensionName] = $extensionData['version'];
                $save = true;

                continue;
            }

            if ($latestExtensionVersions[$extensionName] != $extensionData['version']) {
                $latestExtensionVersions[$extensionName] = $extensionData['version'];

                /*if (!empty($extensionData['notes'])) {
                    //todo: create notification
                }*/

                $save = true;

                //continue;
            }

            /*if (!empty($extensionData['notes'])) {
                //todo: find and modify notification
            }*/
        }

        if ($save) {
            $this->configWriter->set('latestExtensionVersions', $latestExtensionVersions);

            $this->configWriter->save();
        }
    }
}
