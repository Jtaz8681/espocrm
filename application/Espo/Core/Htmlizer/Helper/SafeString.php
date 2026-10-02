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

namespace Espo\Core\Htmlizer\Helper;

use DevTheorem\Handlebars\SafeString as SafeStringHandlebars;

class SafeString
{
    private SafeStringHandlebars $internalSafeString;

    public function __construct(string $value)
    {
        $this->internalSafeString = new SafeStringHandlebars($value);
    }

    public static function create(string $value): self
    {
        return new self($value);
    }

    /**
     * @internal
     */
    public function getInternal(): SafeStringHandlebars
    {
        return $this->internalSafeString;
    }

    public function __toString()
    {
        return (string) $this->internalSafeString;
    }
}
