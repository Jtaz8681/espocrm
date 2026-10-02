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

namespace Espo\Modules\Crm\Tools\Calendar\Items;

use Espo\Core\Field\DateTime;
use Espo\Modules\Crm\Tools\Calendar\Item;

use RuntimeException;
use stdClass;

class Event implements Item
{
    private ?DateTime $start;
    private ?DateTime $end;
    private string $entityType;
    /** @var array<string, mixed> */
    private array $attributes;
    /** @var string[] */
    private array $userIdList = [];
    /** @var array<string, string> */
    private array $userNameMap = [];

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(?DateTime $start, ?DateTime $end, string $entityType, array $attributes)
    {
        $this->start = $start;
        $this->end = $end;
        $this->entityType = $entityType;
        $this->attributes = $attributes;
    }

    public function getRaw(): stdClass
    {
        $obj = (object) [
            'scope' => $this->entityType,
            'dateStart' => $this->start?->toString(),
            'dateEnd' => $this->end?->toString(),
        ];

        if ($this->userIdList !== []) {
            $obj->userIdList = $this->userIdList;
            $obj->userNameMap = (object) $this->userNameMap;
        }

        foreach ($this->attributes as $key => $value) {
            $obj->$key = $obj->$key ?? $value;
        }

        return $obj;
    }

    /**
     * @param mixed $value
     */
    public function withAttribute(string $name, $value): self
    {
        $obj = clone $this;
        $obj->attributes[$name] = $value;

        return $obj;
    }

    public function withId(string $id): self
    {
        $obj = clone $this;
        $obj->attributes['id'] = $id;

        return $obj;
    }

    public function withUserIdAdded(string $userId): self
    {
        $obj = clone $this;
        $obj->userIdList[] = $userId;

        return $obj;
    }

    /**
     * @param array<string, string> $userNameMap
     */
    public function withUserNameMap(array $userNameMap): self
    {
        $obj = clone $this;
        $obj->userNameMap = $userNameMap;

        return $obj;
    }

    public function getId(): string
    {
        $id = $this->attributes['id'] ?? null;

        if (!$id) {
            throw new RuntimeException();
        }

        return $id;
    }

    public function getStart(): ?DateTime
    {
        return $this->start;
    }

    public function getEnd(): ?DateTime
    {
        return $this->end;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string[]
     */
    public function getUserIdList(): array
    {
        return $this->userIdList;
    }

    /**
     * @return mixed
     */
    public function getAttribute(string $name)
    {
        return $this->attributes[$name] ?? null;
    }
}
