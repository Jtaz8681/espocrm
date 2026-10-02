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

namespace Espo\Tools\Attachment;

use Espo\Core\Exceptions\Error;

/**
 * Immutable.
 */
class FieldData
{
    private string $field;
    private ?string $parentType;
    private ?string $relatedType;

    /**
     * @throws Error
     */
    public function __construct(
        string $field,
        ?string $parentType,
        ?string $relatedType
    ) {
        $this->field = $field;
        $this->parentType = $parentType;
        $this->relatedType = $relatedType;

        if (!$parentType && !$relatedType) {
            throw new Error("No parentType and relatedType.");
        }
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getParentType(): ?string
    {
        return $this->parentType;
    }

    public function getRelatedType(): ?string
    {
        return $this->relatedType;
    }
}
