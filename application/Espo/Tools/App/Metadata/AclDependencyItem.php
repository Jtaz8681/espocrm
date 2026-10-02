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

namespace Espo\Tools\App\Metadata;

class AclDependencyItem
{
    /**
     * @param ?string[] $anyScopeList
     */
    public function __construct(
        private string $target,
        private ?string $scope,
        private ?string $field,
        private ?array $anyScopeList = null,
    ) {}

    /**
     * A metadata path to be allowed if a user has access to a specific scope/field.
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * @return ?string[]
     * @since 9.2.5
     */
    public function getAnyScopeList(): ?array
    {
        return $this->anyScopeList;
    }
}
