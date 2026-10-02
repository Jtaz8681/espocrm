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

namespace Espo\Tools\Layout;

class LayoutDefs
{
    /**
     * @param non-empty-string $scope
     * @param non-empty-string $name
     * @param non-empty-string $type
     * @param non-empty-string $label
     */
    public function __construct(
        private string $scope,
        private string $name,
        private string $type,
        private string $label
    ) {}

    /**
     * @return non-empty-string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return non-empty-string
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}
