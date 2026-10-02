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

namespace Espo\Tools\OpenApi\Api;

use Espo\Core\Acl;
use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\Forbidden;
use Espo\Tools\OpenApi\Provider\Params;
use Espo\Tools\OpenApi\ProviderFactory;

/**
 * @noinspection PhpUnused
 */
class GetSpec implements Action
{
    private const string SCOPE = 'OpenApi';

    public function __construct(
        private Acl $acl,
        private ProviderFactory $providerFactory,
    ) {}

    public function process(Request $request): Response
    {
        $this->checkAccess();

        $provider = $this->providerFactory->create();

        $skipCustom = $request->getQueryParam('skipCustom') === 'true';
        $module = $request->getQueryParam('module');

        $params = new Params(
            skipCustom: $skipCustom,
            module: $module,
        );

        $spec = $provider->get($params);

        return ResponseComposer::empty()
            ->writeBody($spec)
            ->setHeader('Content-Type', 'application/json');
    }

    /**
     * @throws Forbidden
     */
    private function checkAccess(): void
    {
        if (!$this->acl->checkScope(self::SCOPE)) {
            throw new Forbidden("No access to OpenApi scope.");
        }
    }
}
