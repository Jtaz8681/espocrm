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

namespace Espo\Classes\FieldProcessing\OAuthAccount;

use Espo\Core\FieldProcessing\Loader;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Entities\OAuthAccount;
use Espo\ORM\Entity;
use Espo\Tools\OAuth\ConfigDataProvider;

/**
 * @implements Loader<OAuthAccount>
 */
class DataLoader implements Loader
{
    public function __construct(
        private ConfigDataProvider $configDataProvider,
    ) {}

    public function process(Entity $entity, Params $params): void
    {
        if (!$entity->get('providerId')) {
            return;
        }

        $provider = $entity->getProvider();

        $scope = null;

        if ($provider->getScopes()) {
            $scope = implode($provider->getScopeSeparator() ?? ' ', $provider->getScopes());
        }

        $data = [
            'endpoint' => $provider->getAuthorizationEndpoint(),
            'clientId' => $provider->getClientId(),
            'redirectUri' => $this->configDataProvider->getRedirectUri(),
            'scope' => $scope,
            'prompt' => $provider->getAuthorizationPrompt(),
            'params' => $provider->getAuthorizationParams(),
        ];

        $entity->set('data', $data);
    }
}
