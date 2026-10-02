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

namespace Espo\Core\Select\Where;

use InvalidArgumentException;

/**
 * Where parameters.
 *
 * Immutable.
 */
class Params
{
    private bool $applyPermissionCheck = false;
    private bool $forbidComplexExpressions = false;

    private function __construct()
    {}

    /**
     * @param array{
     *     applyPermissionCheck?: bool,
     *     forbidComplexExpressions?: bool,
     * } $params
     */
    public static function fromAssoc(array $params): self
    {
        $object = new self();

        $object->applyPermissionCheck = $params['applyPermissionCheck'] ?? false;
        $object->forbidComplexExpressions = $params['forbidComplexExpressions'] ?? false;

        foreach ($params as $key => $value) {
            if (!property_exists($object, $key)) {
                throw new InvalidArgumentException("Unknown parameter '$key'.");
            }
        }

        return $object;
    }

    /**
     * Apply permission check.
     */
    public function applyPermissionCheck(): bool
    {
        return $this->applyPermissionCheck;
    }

    /**
     * Forbid complex expressions.
     */
    public function forbidComplexExpressions(): bool
    {
        return $this->forbidComplexExpressions;
    }
}
