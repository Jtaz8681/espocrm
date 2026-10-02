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

namespace Espo\Tools\LeadCapture;

class ConfirmResult
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_EXPIRED = 'expired';

    public function __construct(
        private string $status,
        private ?string $message,
        private ?string $leadCaptureId = null,
        private ?string $leadCaptureName = null
    ) {}

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getLeadCaptureId(): ?string
    {
        return $this->leadCaptureId;
    }

    public function getLeadCaptureName(): ?string
    {
        return $this->leadCaptureName;
    }
}
