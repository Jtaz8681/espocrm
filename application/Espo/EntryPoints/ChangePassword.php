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

namespace Espo\EntryPoints;

use Espo\Core\Exceptions\BadRequest;
use Espo\Entities\PasswordChangeRequest;
use Espo\Core\Utils\Client\ActionRenderer;
use Espo\Core\EntryPoint\EntryPoint;
use Espo\Core\EntryPoint\Traits\NoAuth;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\ORM\EntityManager;
use Espo\Tools\UserSecurity\Password\ConfigProvider;

class ChangePassword implements EntryPoint
{
    use NoAuth;

    public function __construct(
        private EntityManager $entityManager,
        private ActionRenderer $actionRenderer,
        private ConfigProvider $configProvider,
    ) {}

    public function run(Request $request, Response $response): void
    {
        $requestId = $request->getQueryParam('id');

        if (!$requestId) {
            throw new BadRequest("No request ID.");
        }

        $passwordChangeRequest = $this->entityManager
            ->getRDBRepository(PasswordChangeRequest::ENTITY_TYPE)
            ->where([
                'requestId' => $requestId,
            ])
            ->findOne();

        $strengthParams = [
            'passwordGenerateLength' => $this->configProvider->getGenerateLength(),
            'passwordGenerateLetterCount' => $this->configProvider->getGenerateLetterCount(),
            'generateNumberCount' => $this->configProvider->getGenerateNumberCount(),
            'passwordStrengthLength' => $this->configProvider->getStrengthLength(),
            'passwordStrengthLetterCount' => $this->configProvider->getStrengthLetterCount(),
            'passwordStrengthNumberCount' => $this->configProvider->getStrengthNumberCount(),
            'passwordStrengthBothCases' => $this->configProvider->getStrengthBothCases(),
            'passwordStrengthSpecialCharacterCount' => $this->configProvider->getStrengthSpecialCharacterCount(),
        ];

        $options = [
            'id' => $requestId,
            'strengthParams' => $strengthParams,
            'notFound' => !$passwordChangeRequest,
        ];

        $params = new ActionRenderer\Params('controllers/password-change-request', 'passwordChange', $options);

        $this->actionRenderer->write($response, $params);
    }
}
