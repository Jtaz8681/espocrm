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

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;

use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Controllers\Record;
use Espo\Core\Exceptions\Error;

use Espo\Tools\LeadCapture\Service;
use Espo\Tools\LeadCapture\CaptureService as CaptureService;
use stdClass;

class LeadCapture extends Record
{
    /**
     * @throws BadRequest
     * @throws NotFound
     * @throws Error
     */
    public function postActionLeadCapture(Request $request, Response $response): bool
    {
        $data = $request->getParsedBody();
        $apiKey = $request->getRouteParam('apiKey');

        if (!$apiKey) {
            throw new BadRequest('No API key provided.');
        }

        $allowOrigin = $this->config->get('leadCaptureAllowOrigin', '*');

        $response->setHeader('Access-Control-Allow-Origin', $allowOrigin);

        $this->getCaptureService()->capture($apiKey, $data);

        return true;
    }

    /**
     * @throws BadRequest
     * @throws NotFound
     */
    public function optionsActionLeadCapture(Request $request, Response $response): bool
    {
        $apiKey = $request->getRouteParam('apiKey');

        if (!$apiKey) {
            throw new BadRequest('No API key provided.');
        }

        if (!$this->getLeadCaptureService()->isApiKeyValid($apiKey)) {
            throw new NotFound();
        }

        $allowOrigin = $this->config->get('leadCaptureAllowOrigin', '*');

        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Accept');
        $response->setHeader('Access-Control-Allow-Origin', $allowOrigin);
        $response->setHeader('Access-Control-Allow-Methods', 'POST');

        return true;
    }

    /**
     * @throws BadRequest
     * @throws NotFound
     * @throws Forbidden
     */
    public function postActionGenerateNewApiKey(Request $request): stdClass
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest();
        }

        return $this->getLeadCaptureService()
            ->generateNewApiKeyForEntity($data->id)
            ->getValueMap();
    }

    /**
     * @throws BadRequest
     * @throws NotFound
     * @throws Forbidden
     */
    public function postActionGenerateNewFormId(Request $request): stdClass
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest();
        }

        return $this->getLeadCaptureService()
            ->generateNewFormIdForEntity($data->id)
            ->getValueMap();
    }

    /**
     * @return stdClass[]
     * @throws Forbidden
     */
    public function getActionSmtpAccountDataList(): array
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }

        return $this->getLeadCaptureService()->getSmtpAccountDataList();
    }

    private function getCaptureService(): CaptureService
    {
        return $this->injectableFactory->create(CaptureService::class);
    }

    private function getLeadCaptureService(): Service
    {
        return $this->injectableFactory->create(Service::class);
    }
}
