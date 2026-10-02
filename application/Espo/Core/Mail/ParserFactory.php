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

namespace Espo\Core\Mail;

use Espo\Core\InjectableFactory;
use Espo\Core\Mail\Parsers\MailMimeParser;

class ParserFactory
{
    protected const DEFAULT_PARSER_CLASS_NAME = MailMimeParser::class;

    public function __construct(private InjectableFactory $injectableFactory)
    {}

    public function create(): Parser
    {
        return $this->injectableFactory->create(self::DEFAULT_PARSER_CLASS_NAME);
    }
}
