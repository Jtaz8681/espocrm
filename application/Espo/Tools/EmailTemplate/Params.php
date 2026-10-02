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

namespace Espo\Tools\EmailTemplate;

/**
 * Immutable.
 */
class Params
{
    private bool $applyAcl = false;
    private bool $copyAttachments = false;

    public function applyAcl(): bool
    {
        return $this->applyAcl;
    }

    public function copyAttachments(): bool
    {
        return $this->copyAttachments;
    }

    /**
     * To apply ACL.
     */
    public function withApplyAcl(bool $applyAcl = true): self
    {
        $obj = clone $this;
        $obj->applyAcl = $applyAcl;

        return $obj;
    }

    /**
     * To copy template attachments records. Not needed if an email not supposed to be stored.
     */
    public function withCopyAttachments(bool $copyAttachments = true): self
    {
        $obj = clone $this;
        $obj->copyAttachments = $copyAttachments;

        return $obj;
    }

    public static function create(): self
    {
        return new self();
    }
}
