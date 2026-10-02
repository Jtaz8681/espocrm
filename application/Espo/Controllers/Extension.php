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

use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Controllers\RecordBase;
use Espo\Core\Upgrades\ExtensionManager;

use stdClass;

class Extension extends RecordBase
{
    protected function checkAccess(): bool
    {
        return $this->user->isAdmin();
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     */
    public function postActionUpload(Request $request): stdClass
    {
        $this->assertUpgradeAllowed();

        $body = $request->getBodyContents();

        if ($body === null) {
            throw new BadRequest();
        }

        $manager = $this->createManager();

        $id = $manager->upload($body);

        $manifest = $manager->getManifest();

        return (object) [
            'id' => $id,
            'version' => $manifest['version'],
            'name' => $manifest['name'],
            'description' => $manifest['description'],
        ];
    }

    /**
     * @throws Forbidden
     * @throws Error
     */
    public function postActionInstall(Request $request): bool
    {
        $data = $request->getParsedBody();

        if ($this->config->get('restrictedMode')) {
            throw new Forbidden();
        }

        $manager = $this->createManager();

        $manager->install(get_object_vars($data));

        return true;
    }

    /**
     * @throws Forbidden
     * @throws Error
     */
    public function postActionUninstall(Request $request): bool
    {
        $data = $request->getParsedBody();

        if ($this->config->get('restrictedMode')) {
            throw new Forbidden();
        }

        $manager = $this->createManager();

        $manager->uninstall(get_object_vars($data));

        return true;
    }

    /**
     * @throws Forbidden
     * @throws Error
     */
    public function deleteActionDelete(Request $request, Response $response): bool
    {
        $params = $request->getRouteParams();

        $this->assertUpgradeAllowed();

        $manager = $this->createManager();

        $manager->delete($params);

        return true;
    }

    public function postActionCreate(Request $request, Response $response): stdClass
    {
        throw new Forbidden();
    }

    public function putActionUpdate(Request $request, Response $response): stdClass
    {
        throw new Forbidden();
    }

    private function createManager(): ExtensionManager
    {
        return $this->injectableFactory->create(ExtensionManager::class);
    }

    /**
     * @throws Forbidden
     */
    private function assertUpgradeAllowed(): void
    {
        if ($this->config->get('restrictedMode')) {
            throw new Forbidden("Not allowed in restricted mode.");
        }

        if ($this->config->get('adminExtensionUpload') !== true) {
            throw new Forbidden("Cannot upload extensions as `adminExtensionUpload` is not enabled.");
        }

        if ($this->config->get('adminUpgradeDisabled')) {
            throw new Forbidden("Disabled with `adminUpgradeDisabled` parameter.");
        }
    }
}
