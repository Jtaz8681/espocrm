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

namespace Espo\Core\Formula\Functions\LogGroup;

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Core\Utils\Log;
use Psr\Log\LogLevel;
use stdClass;

class InfoType implements Func
{
    protected string $level = LogLevel::INFO;

    public function __construct(
        private Log $log
    ) {}

    public function process(EvaluatedArgumentList $arguments): mixed
    {
        if (count($arguments) < 1) {
            throw TooFewArguments::create(1);
        }

        $message = $arguments[0];
        $context = $arguments[1] ?? (object) [];

        if (!is_string($message)) {
            throw BadArgumentType::create(1, 'string');
        }

        if (!$context instanceof stdClass) {
            throw BadArgumentType::create(2, 'object');
        }

        $context = array_merge(get_object_vars($context), ['context' => 'formula']);

        $this->log->log($this->level, $message, $context);

        return null;
    }
}
