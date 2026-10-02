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

namespace Espo\Core\Formula\Functions\PasswordGroup;

use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Formula\Processor;

use Espo\Core\Utils\PasswordHash;
use SensitiveParameter;

class HashType extends BaseFunction
{
    protected PasswordHash $passwordHash;

    public function __construct(Processor $processor, PasswordHash $passwordHash)
    {
        $this->processor = $processor;
        $this->passwordHash = $passwordHash;
    }

    public function process(#[SensitiveParameter] ArgumentList $args)
    {
        if (count($args) < 1) {
            $this->throwTooFewArguments();
        }

        $password = $this->evaluate($args[0]);

        if (!is_string($password)) {
            $this->throwBadArgumentType(1, 'string');
        }

        return $this->passwordHash->hash($password);
    }
}
