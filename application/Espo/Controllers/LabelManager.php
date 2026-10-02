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

use Espo\Core\Api\Request;
use Espo\Core\DataManager;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Entities\User;
use Espo\Tools\LabelManager\LabelManager as LabelManagerTool;

use stdClass;

/**
 * @noinspection PhpUnused
 */
class LabelManager
{
    /**
     * @throws Forbidden
     */
    public function __construct(
        private User $user,
        private DataManager $dataManager,
        private LabelManagerTool $labelManagerTool
    ) {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }
    }

    /**
     * @return string[]
     */
    public function postActionGetScopeList(): array
    {
        return $this->labelManagerTool->getScopeList();
    }

    /**
     * @throws BadRequest
     */
    public function postActionGetScopeData(Request $request): stdClass
    {
        $data = $request->getParsedBody();

        $language = $data->language ?? null;
        $scope = $data->scope ?? null;

        if (!$scope || !$language) {
            throw new BadRequest();
        }

        if (!is_string($scope) || !is_string($language)) {
            throw new BadRequest();
        }

        if (basename($scope) !== $scope || basename($language) !== $language) {
            throw new BadRequest();
        }

        return $this->labelManagerTool->getScopeData($language, $scope);
    }

    /**
     * @throws BadRequest
     * @throws Error
     */
    public function postActionSaveLabels(Request $request): stdClass
    {
        $data = $request->getParsedBody();

        $language = $data->language ?? null;
        $scope = $data->scope ?? null;

        if (!$scope || !$language || !isset($data->labels)) {
            throw new BadRequest();
        }

        if (!is_string($scope) || !is_string($language)) {
            throw new BadRequest();
        }

        if (basename($scope) !== $scope || basename($language) !== $language) {
            throw new BadRequest();
        }

        $labels = get_object_vars($data->labels);

        $returnData = $this->labelManagerTool->saveLabels($language, $scope, $labels);

        $this->dataManager->clearCache();

        return $returnData;
    }
}
