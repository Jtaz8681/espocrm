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

namespace Espo\Tools\LeadCapture\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Tools\LeadCapture\CaptureService;

/**
 * @noinspection PhpUnused
 */
class PostForm implements Action
{
    public function __construct(
        private CaptureService $service,
    ) {}

    public function process(Request $request): Response
    {
        $data = $request->getParsedBody();
        $id = $request->getRouteParam('id') ?? throw new BadRequest();
        $captchaToken = $request->getHeader('X-Captcha-Token');

        $result = $this->service->captureForm($id, $data, $captchaToken);

        return ResponseComposer::json([
            'redirectUrl' => $result->redirectUrl,
        ]);
    }
}
