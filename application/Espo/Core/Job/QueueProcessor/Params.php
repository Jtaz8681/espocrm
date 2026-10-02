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

namespace Espo\Core\Job\QueueProcessor;

class Params
{
    private bool $useProcessPool = false;
    private bool $noLock = false;
    private ?string $queue = null;
    private ?string $group = null;
    private int $limit = 0;
    /** @var ?Params[] */
    private ?array $subQueueParamsList = null;
    private float $weight = 1.0;

    public function withUseProcessPool(bool $useProcessPool): self
    {
        $obj = clone $this;
        $obj->useProcessPool = $useProcessPool;

        return $obj;
    }

    public function withNoLock(bool $noLock): self
    {
        $obj = clone $this;
        $obj->noLock = $noLock;

        return $obj;
    }

    public function withQueue(?string $queue): self
    {
        $obj = clone $this;
        $obj->queue = $queue;

        return $obj;
    }

    public function withGroup(?string $group): self
    {
        $obj = clone $this;
        $obj->group = $group;

        return $obj;
    }

    public function withLimit(int $limit): self
    {
        $obj = clone $this;
        $obj->limit = $limit;

        return $obj;
    }

    public function withWeight(float $weight): self
    {
        $obj = clone $this;
        $obj->weight = $weight;

        return $obj;
    }

    /**
     * @param ?Params[] $subQueueParamsList
     */
    public function withSubQueueParamsList(?array $subQueueParamsList): self
    {
        $obj = clone $this;
        $obj->subQueueParamsList = $subQueueParamsList;

        return $obj;
    }

    public function useProcessPool(): bool
    {
        return $this->useProcessPool;
    }

    public function noLock(): bool
    {
        return $this->noLock;
    }

    public function getQueue(): ?string
    {
        return $this->queue;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @return ?Params[]
     */
    public function getSubQueueParamsList(): ?array
    {
        return $this->subQueueParamsList;
    }

    public static function create(): self
    {
        return new self();
    }
}
