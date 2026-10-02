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

namespace Espo\Core\Utils\Client;

use Espo\Core\Api\Response;
use Espo\Core\Utils\Client\ActionRenderer\Params;
use Espo\Core\Utils\Json;
use Espo\Core\Utils\ClientManager;

/**
 * Renders a front-end page that executes a controller action. Utilized by entry points.
 */
class ActionRenderer
{

    public function __construct(private ClientManager $clientManager)
    {}

    /**
     * Writes to a body.
     */
    public function write(Response $response, Params $params): void
    {
        $body = $this->render(
            controller: $params->getController(),
            action: $params->getAction(),
            data: $params->getData(),
            initAuth: $params->initAuth(),
            scripts: $params->getScripts(),
            pageTitle: $params->getPageTitle(),
            theme: $params->getTheme(),
        );

        $securityParams = new SecurityParams(
            frameAncestors: $params->getFrameAncestors(),
        );

        $this->clientManager->writeHeaders($response, $securityParams);
        $response->writeBody($body);
    }

    /**
     * @param ?array<string, mixed> $data
     * @param Script[] $scripts
     */
    private function render(
        string $controller,
        string $action,
        ?array $data,
        bool $initAuth,
        array $scripts,
        ?string $pageTitle,
        ?string $theme,
    ): string {

        $encodedData = Json::encode($data);

        $initAuthPart = $initAuth ? "app.initAuth();" : '';

        $script =
            "
                {$initAuthPart}
                app.doAction({
                    controllerClassName: '$controller',
                    action: '$action',
                    options: $encodedData,
                });
            ";

        $params = new RenderParams(
            runScript: $script,
            scripts: $scripts,
            pageTitle: $pageTitle,
            theme: $theme,
        );

        return $this->clientManager->render($params);
    }
}
