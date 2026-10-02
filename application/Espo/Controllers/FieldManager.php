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

use Espo\Entities\User;
use Espo\Tools\FieldManager\FieldManager as FieldManagerTool;
use Espo\Core\Api\Request;
use Espo\Core\DataManager;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;

/**
 * @noinspection PhpUnused
 */
class FieldManager
{
    /**
     * @throws Forbidden
     */
    public function __construct(
        private User $user,
        private DataManager $dataManager,
        private FieldManagerTool $fieldManagerTool,
    ) {
        $this->checkControllerAccess();
    }

    /**
     * @throws Forbidden
     */
    protected function checkControllerAccess(): void
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }
    }

    /**
     * @return array<string, mixed>
     * @throws BadRequest
     * @throws Error
     */
    public function getActionRead(Request $request): array
    {
        $scope = $request->getRouteParam('scope');
        $name = $request->getRouteParam('name');

        if (!$scope || !$name) {
            throw new BadRequest();
        }

        return $this->fieldManagerTool->read($scope, $name);
    }

    /**
     * @return array<string, mixed>
     * @throws BadRequest
     * @throws Conflict
     * @throws Error
     */
    public function postActionCreate(Request $request): array
    {
        $data = $request->getParsedBody();

        $scope = $request->getRouteParam('scope');
        $name = $data->name ?? null;

        if (!$scope || !$name) {
            throw new BadRequest();
        }

        $fieldManagerTool = $this->fieldManagerTool;

        $name = $fieldManagerTool->create($scope, $name, get_object_vars($data));

        try {
            $this->rebuild($scope);
        } catch (Error $e) {
            $fieldManagerTool->delete($scope, $name);

            throw new Error($e->getMessage());
        }

        return $fieldManagerTool->read($scope, $name);
    }

    /**
     * @return array<string, mixed>
     * @throws BadRequest
     * @throws Error
     */
    public function patchActionUpdate(Request $request): array
    {
        return $this->putActionUpdate($request);
    }

    /**
     * @return array<string, mixed>
     * @throws BadRequest
     * @throws Error
     */
    public function putActionUpdate(Request $request): array
    {
        $data = $request->getParsedBody();

        $scope = $request->getRouteParam('scope');
        $name = $request->getRouteParam('name');

        if (!$scope || !$name) {
            throw new BadRequest();
        }

        $fieldManagerTool = $this->fieldManagerTool;

        $fieldManagerTool->update($scope, $name, get_object_vars($data));

        if ($fieldManagerTool->isChanged()) {
            $this->rebuild($scope);
        } else {
            $this->dataManager->clearCache();
        }

        return $fieldManagerTool->read($scope, $name);
    }

    /**
     * @throws BadRequest
     * @throws Error
     */
    public function deleteActionDelete(Request $request): bool
    {
        $scope = $request->getRouteParam('scope');
        $name = $request->getRouteParam('name');

        if (!$scope || !$name) {
            throw new BadRequest();
        }

        $this->fieldManagerTool->delete($scope, $name);

        $this->dataManager->clearCache();
        $this->dataManager->rebuildMetadata();

        return true;
    }

    /**
     * @throws BadRequest
     * @throws Error
     */
    public function postActionResetToDefault(Request $request): bool
    {
        $data = $request->getParsedBody();

        $scope = $data->scope ?? null;
        $name = $data->name ?? null;

        if (!$scope || !$name) {
            throw new BadRequest();
        }

        if (!is_string($scope) || !is_string($name)) {
            throw new BadRequest();
        }

        $this->fieldManagerTool->resetToDefault($scope, $name);

        $this->rebuild($scope);

        return true;
    }

    /**
     * @throws Error
     */
    private function rebuild(string $scope): void
    {
        $this->dataManager->rebuild([$scope]);
    }
}
