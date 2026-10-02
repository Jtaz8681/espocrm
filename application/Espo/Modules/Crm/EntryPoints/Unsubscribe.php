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

namespace Espo\Modules\Crm\EntryPoints;

use Espo\Core\Utils\Client\ActionRenderer;
use Espo\Modules\Crm\Tools\MassEmail\UnsubscribeService;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\EntryPoint\EntryPoint;
use Espo\Core\EntryPoint\Traits\NoAuth;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Utils\Metadata;

/**
 * @noinspection PhpUnused
 */
class Unsubscribe implements EntryPoint
{
    use NoAuth;

    public function __construct(
        private Metadata $metadata,
        private ActionRenderer $actionRenderer,
        private UnsubscribeService $unsubscribeService
    ) {}

    /**
     * @throws BadRequest
     * @throws NotFound
     */
    public function run(Request $request, Response $response): void
    {
        $id = $request->getQueryParam('id') ?? null;
        $emailAddress = $request->getQueryParam('emailAddress') ?? null;
        $hash = $request->getQueryParam('hash') ?? null;

        if ($emailAddress && $hash) {
            $this->processWithHash($response, $emailAddress, $hash);

            return;
        }

        if (!$id) {
            throw new BadRequest("No id.");
        }

        $this->display($response, [
            'queueItemId' => $id,
            'isSubscribed' => $this->unsubscribeService->isSubscribed($id),
        ]);
    }

    /**
     * @param array<string, mixed> $actionData
     */
    private function display(Response $response, array $actionData): void
    {
        $data = [
            'actionData' => $actionData,
            'view' => $this->metadata->get(['clientDefs', 'Campaign', 'unsubscribeView']),
            'template' => $this->metadata->get(['clientDefs', 'Campaign', 'unsubscribeTemplate']),
        ];

        $params = ActionRenderer\Params::create('crm:controllers/unsubscribe', 'unsubscribe', $data);

        $this->actionRenderer->write($response, $params);
    }

    /**
     * @throws NotFound
     */
    private function processWithHash(Response $response, string $emailAddress, string $hash): void
    {
        $this->display($response, [
            'emailAddress' => $emailAddress,
            'hash' => $hash,
            'isSubscribed' => $this->unsubscribeService->isSubscribedWithHash($emailAddress, $hash),
        ]);
    }
}
