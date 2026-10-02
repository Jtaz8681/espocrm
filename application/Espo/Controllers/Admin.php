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

namespace Espo\Controllers;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Container;
use Espo\Core\DataManager;
use Espo\Core\Api\Request;
use Espo\Core\Utils\Config;
use Espo\Tools\AdminNotifications\Manager;
use Espo\Core\Utils\SystemRequirements;
use Espo\Core\Utils\ScheduledJob;
use Espo\Core\Upgrades\UpgradeManager;
use Espo\Entities\User;

class Admin
{
    /**
     * @throws Forbidden
     */
    public function __construct(
        private Container $container,
        private Config $config,
        private User $user,
        private Manager $adminNotificationManager,
        private SystemRequirements $systemRequirements,
        private ScheduledJob $scheduledJob,
        private DataManager $dataManager,
        private Config\SystemConfig $systemConfig,
    ) {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }
    }

    /**
     * @throws Error
     */
    public function postActionRebuild(): bool
    {
        $this->dataManager->rebuild();

        return true;
    }

    /**
     * @throws Error
     */
    public function postActionClearCache(): bool
    {
        $this->dataManager->clearCache();

        return true;
    }

    /**
     * @return string[]
     */
    public function getActionJobs(): array
    {
        return $this->scheduledJob->getAvailableList();
    }

    /**
     * @return object{
     *   id: string,
     *   version: string,
     * }
     * @throws Forbidden
     * @throws Error
     * @throws BadRequest
     */
    public function postActionUploadUpgradePackage(Request $request): object
    {
        $this->assertUpgradeAllowed();

        $data = $request->getBodyContents();

        if (!$data) {
            throw new BadRequest();
        }

        $upgradeManager = new UpgradeManager($this->container);

        $upgradeId = $upgradeManager->upload($data);
        $manifest = $upgradeManager->getManifest();

        return (object) [
            'id' => $upgradeId,
            'version' => $manifest['version'],
        ];
    }

    /**
     * @throws Forbidden
     * @throws Error
     */
    public function postActionRunUpgrade(Request $request): bool
    {
        $data = $request->getParsedBody();

        $this->assertUpgradeAllowed();

        $upgradeManager = new UpgradeManager($this->container);

        $upgradeManager->install(get_object_vars($data));

        return true;
    }

    /**
     * @return object{
     *     message: string,
     *     command: string,
     * }
     */
    public function getActionCronMessage(): object
    {
        return (object) $this->scheduledJob->getSetupMessage();
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     type: string,
     *     message: string,
     * }>
     */
    public function getActionAdminNotificationList(): array
    {
        return $this->adminNotificationManager->getNotificationList();
    }

    /**
     * @return object{
     *     php: array<string, array<string, mixed>>,
     *     database: array<string, array<string, mixed>>,
     *     permission: array<string, array<string, mixed>>,
     * }
     * @throws Forbidden
     */
    public function getActionSystemRequirementList(): object
    {
        if (!$this->user->isSuperAdmin() && $this->systemConfig->isRestrictedMode()) {
            throw new Forbidden();
        }

        return (object) $this->systemRequirements->getAllRequiredList();
    }

    /**
     * @throws Forbidden
     */
    private function assertUpgradeAllowed(): void
    {
        if ($this->config->get('restrictedMode')) {
            throw new Forbidden("Not allowed in restricted mode.");
        }

        if ($this->config->get('adminUpgrade') !== true) {
            throw new Forbidden("Cannot upgrade via the UI as `adminUpgrade` is not enabled.");
        }

        if ($this->config->get('adminUpgradeDisabled')) {
            throw new Forbidden("Disabled with `adminUpgradeDisabled` parameter.");
        }
    }
}
