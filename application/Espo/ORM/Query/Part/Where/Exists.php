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

namespace Espo\ORM\Query\Part\Where;

use Espo\ORM\Query\Part\WhereItem;
use Espo\ORM\Query\Select;

/**
 * An EXISTS-operator. Immutable.
 */
class Exists implements WhereItem
{
    private function __construct(private Select $rawValue) {}

    public function getRaw(): array
    {
        return ['EXISTS' => $this->getRawValue()];
    }

    public function getRawKey(): string
    {
        return 'EXISTS';
    }

    public function getRawValue(): Select
    {
        return $this->rawValue;
    }

    public static function create(Select $subQuery): self
    {
        return new self($subQuery);
    }
}
