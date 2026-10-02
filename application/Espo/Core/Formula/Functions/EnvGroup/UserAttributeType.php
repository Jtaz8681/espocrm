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

namespace Espo\Core\Formula\Functions\EnvGroup;

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Entities\User;

class UserAttributeType implements Func
{
    /** @var string[] */
    private array $forbiddenAttributeList = [
        'password',
        'apiKey',
        'secretKey',
    ];

    public function __construct(
        private User $user
    ) {}

    public function process(EvaluatedArgumentList $arguments): mixed
    {
        if (count($arguments) < 1) {
            throw TooFewArguments::create(1);
        }

        $attribute = $arguments[0];

        if (!is_string($attribute)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (in_array($attribute, $this->forbiddenAttributeList)) {
            return null;
        }

        return $this->user->get($attribute);
    }
}
