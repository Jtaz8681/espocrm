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

namespace Espo\Modules\Crm\Tools\Calendar;

use Espo\Core\Field\DateTime;

class FetchParams
{
    private DateTime $from;
    private DateTime $to;
    private bool $isAgenda = false;
    private bool $skipAcl = false;
    /** @var ?string[] */
    private ?array $scopeList = null;
    private bool $workingTimeRanges = false;
    private bool $workingTimeRangesInverted = false;

    public function __construct(DateTime $from, DateTime $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public static function create(DateTime $from, DateTime $to): self
    {
        return new self($from, $to);
    }

    public function withFrom(DateTime $from): self
    {
        $obj = clone $this;
        $obj->from = $from;

        return $obj;
    }

    public function withTo(DateTime $to): self
    {
        $obj = clone $this;
        $obj->to = $to;

        return $obj;
    }

    public function withIsAgenda(bool $isAgenda = true): self
    {
        $obj = clone $this;
        $obj->isAgenda = $isAgenda;

        return $obj;
    }

    public function withSkipAcl(bool $skipAcl = true): self
    {
        $obj = clone $this;
        $obj->skipAcl = $skipAcl;

        return $obj;
    }

    /**
     * @param ?string[] $scopeList
     */
    public function withScopeList(?array $scopeList): self
    {
        $obj = clone $this;
        $obj->scopeList = $scopeList;

        return $obj;
    }

    public function withWorkingTimeRanges(bool $workingTimeRanges = true): self
    {
        $obj = clone $this;
        $obj->workingTimeRanges = $workingTimeRanges;

        return $obj;
    }

    public function withWorkingTimeRangesInverted(bool $workingTimeRangesInverted = true): self
    {
        $obj = clone $this;
        $obj->workingTimeRangesInverted = $workingTimeRangesInverted;

        return $obj;
    }

    public function getFrom(): DateTime
    {
        return $this->from;
    }

    public function getTo(): DateTime
    {
        return $this->to;
    }

    public function isAgenda(): bool
    {
        return $this->isAgenda;
    }

    public function skipAcl(): bool
    {
        return $this->skipAcl;
    }

    /**
     * @return ?string[]
     */
    public function getScopeList(): ?array
    {
        return $this->scopeList;
    }

    public function workingTimeRanges(): bool
    {
        return $this->workingTimeRanges;
    }

    public function workingTimeRangesInverted(): bool
    {
        return $this->workingTimeRangesInverted;
    }
}
