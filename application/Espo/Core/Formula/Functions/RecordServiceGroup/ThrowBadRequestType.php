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

namespace Espo\Core\Formula\Functions\RecordServiceGroup;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error\Body;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Exceptions\WrapperException;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Utils\Json;

/**
 * @noinspection PhpUnused
 */
class ThrowBadRequestType extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        if (empty($this->getVariables()->__isRecordService)) {
            $this->throwError("Can be called only from API script.");
        }

        $message = isset($args[0]) ? $this->evaluate($args[0]) : '';
        $body = isset($args[1]) ? $this->evaluate($args[1]) : null;

        if ($body !== null) {
            throw WrapperException::create(
                BadRequest::createWithBody($message, Json::encode($body))
            );
        }

        throw WrapperException::create(
            BadRequest::createWithBody($message, Body::create()->withMessage($message))
        );
    }
}
