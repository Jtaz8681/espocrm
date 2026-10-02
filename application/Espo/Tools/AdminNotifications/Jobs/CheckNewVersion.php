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
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Tools\AdminNotifications\LatestReleaseDataRequester;

/**
 * Checking for a new EspoCRM version.
 */
class CheckNewVersion implements JobDataLess
{
    public function __construct(
        private Config $config,
        private ConfigWriter $configWriter,
        private LatestReleaseDataRequester $requester
    ) {}

    public function run(): void
    {
        if (
            !$this->config->get('adminNotifications') ||
            !$this->config->get('adminNotificationsNewVersion')
        ) {
            return;
        }

        $latestRelease = $this->requester->request();

        if ($latestRelease === null) {
            return;
        }

        if (empty($latestRelease['version'])) {
            // @todo Check the logic. WTF?
            $this->configWriter->set('latestVersion', $latestRelease['version']);

            $this->configWriter->save();

            return;
        }

        if ($this->config->get('latestVersion') != $latestRelease['version']) {
            $this->configWriter->set('latestVersion', $latestRelease['version']);

            /*if (!empty($latestRelease['notes'])) {
                // @todo Create a notification.
            }*/

            $this->configWriter->save();

            return;
        }

        /*if (!empty($latestRelease['notes'])) {
            // @todo Find and modify notification.
        }*/
    }
}
