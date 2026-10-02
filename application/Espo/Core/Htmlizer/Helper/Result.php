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

class Result
{
    /**
     * @var string|SafeString|null
     */
    private $value = null;

    private function __construct() {}

    public static function createSafeString(string $value): self
    {
        $obj = new self();
        $obj->value = new SafeString($value);

        return $obj;
    }

    public static function createEmpty(): self
    {
        $obj = new self();
        $obj->value = '';

        return $obj;
    }

    public static function create(string $value): self
    {
        $obj = new self();
        $obj->value = $value;

        return $obj;
    }

    /**
     * @return SafeString|string
     */
    public function getValue()
    {
        if ($this->value instanceof SafeString) {
            return $this->value;
        }

        return (string) $this->value;
    }
}
