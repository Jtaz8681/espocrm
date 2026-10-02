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

namespace Espo\Core\Formula\Functions\ExtGroup\SecretGroup;

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\Error;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Tools\AppSecret\SecretProvider;

/**
 * @noinspection PhpUnused
 */
class GetType implements Func
{
    public function __construct(private SecretProvider $secretProvider)
    {}

    public function process(EvaluatedArgumentList $arguments): string
    {
        if (count($arguments) < 1) {
            throw TooFewArguments::create(1);
        }

        $name = $arguments[0];

        if (!is_string($name)) {
            throw BadArgumentType::create(1, 'string');
        }

        $secret = $this->secretProvider->get($name);

        if ($secret === null) {
            throw new Error("No secret '$name'.");
        }

        return $secret;
    }
}
