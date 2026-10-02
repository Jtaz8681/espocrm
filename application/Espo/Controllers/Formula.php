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
use Espo\Core\Api\RequestWrapper;
use Espo\Core\Exceptions\NotFoundSilent;
use Espo\Tools\Formula\Service;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\ForbiddenSilent;
use Espo\Entities\User;
use Espo\Core\Field\LinkParent;

use stdClass;

class Formula
{

    /**
     * @throws ForbiddenSilent
     */
    public function __construct(
        private Service $service,
        User $user,
    ) {
        if (!$user->isAdmin()) {
            throw new ForbiddenSilent();
        }
    }

    /**
     * @throws BadRequest
     */
    public function postActionCheckSyntax(Request $request): stdClass
    {
        $expression = $request->getParsedBody()->expression ?? null;

        if (!$expression || !is_string($expression)) {
            throw new BadRequest("No or non-string expression.");
        }

        return $this->service->checkSyntax($expression)->toStdClass();
    }

    /**
     * @throws BadRequest
     * @throws NotFoundSilent
     */
    public function postActionRun(Request $request): stdClass
    {
        if ($request instanceof RequestWrapper && $request->getContentType() !== 'application/json') {
            throw new BadRequest();
        }

        $expression = $request->getParsedBody()->expression ?? null;
        $targetType = $request->getParsedBody()->targetType ?? null;
        $targetId = $request->getParsedBody()->targetId ?? null;

        if (!$expression || !is_string($expression)) {
            throw new BadRequest("No or non-string expression.");
        }

        $targetLink = null;

        if ($targetType && $targetId) {
            $targetLink = LinkParent::create($targetType, $targetId);
        }

        return $this->service->run($expression, $targetLink)->toStdClass();
    }
}
