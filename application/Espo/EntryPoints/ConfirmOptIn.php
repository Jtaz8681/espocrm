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

use Espo\Tools\LeadCapture\CaptureService as Service;

use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\EntryPoint\EntryPoint;
use Espo\Core\EntryPoint\Traits\NoAuth;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Utils\Client\ActionRenderer;
use Espo\Tools\LeadCapture\ConfirmResult;
use LogicException;

class ConfirmOptIn implements EntryPoint
{
    use NoAuth;

    private Service $service;
    private ActionRenderer $actionRenderer;

    public function __construct(Service $service, ActionRenderer $actionRenderer)
    {
        $this->service = $service;
        $this->actionRenderer = $actionRenderer;
    }

    /**
     * @throws BadRequest
     * @throws Error
     * @throws NotFound
     */
    public function run(Request $request, Response $response): void
    {
        $id = $request->getQueryParam('id');

        if (!$id) {
            throw new BadRequest("No id.");
        }

        $result = $this->service->confirmOptIn($id);

        $action = null;

        if ($result->getStatus() === ConfirmResult::STATUS_EXPIRED) {
            $action = 'optInConfirmationExpired';
        }

        if ($result->getStatus() === ConfirmResult::STATUS_SUCCESS) {
            $action = 'optInConfirmationSuccess';
        }

        if (!$action) {
            throw new LogicException();
        }

        $data = [
            'status' => $result->getStatus(),
            'message' => $result->getMessage(),
            'leadCaptureId' => $result->getLeadCaptureId(),
            'leadCaptureName' => $result->getLeadCaptureName(),
        ];

        $params = new ActionRenderer\Params('controllers/lead-capture-opt-in-confirmation', $action, $data);

        $this->actionRenderer->write($response, $params);
    }
}
