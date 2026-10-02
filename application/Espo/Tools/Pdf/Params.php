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

namespace Espo\Tools\Pdf;

/**
 * Immutable.
 */
class Params
{
    private bool $applyAcl = false;
    private bool $pdfA = false;

    public function applyAcl(): bool
    {
        return $this->applyAcl;
    }

    public function isPdfA(): bool
    {
        return $this->pdfA;
    }

    public function withPdfA(bool $pdfA = true): self
    {
        $obj = clone $this;
        $obj->pdfA = $pdfA;

        return $obj;
    }

    public function withAcl(bool $applyAcl = true): self
    {
        $obj = clone $this;
        $obj->applyAcl = $applyAcl;

        return $obj;
    }

    public static function create(): self
    {
        return new self();
    }
}
