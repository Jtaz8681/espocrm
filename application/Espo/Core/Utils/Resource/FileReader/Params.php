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

namespace Espo\Core\Utils\Resource\FileReader;

/**
 * Immutable.
 */
class Params
{
    private ?string $scope = null;
    private ?string $moduleName = null;

    public static function create(): self
    {
        return new self();
    }

    public function withScope(?string $scope): self
    {
        $obj = clone $this;
        $obj->scope = $scope;

        return $obj;
    }

    public function withModuleName(?string $moduleName): self
    {
        $obj = clone $this;
        $obj->moduleName = $moduleName;

        return $obj;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function getModuleName(): ?string
    {
        return $this->moduleName;
    }
}
