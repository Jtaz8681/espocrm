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

namespace Espo\Tools\LinkManager;

use Espo\Tools\LinkManager\ParamsBuilder;

/**
 * Immutable.
 */
class Params
{
    private string $type;
    private string $entityType;
    private string $link;
    private string $foreignLink;
    private ?string $foreignEntityType;
    private ?string $name;

    public function __construct(
        string $type,
        string $entityType,
        string $link,
        ?string $foreignEntityType,
        string $foreignLink,
        ?string $name
    ) {
        $this->type = $type;
        $this->entityType = $entityType;
        $this->link = $link;
        $this->foreignEntityType = $foreignEntityType;
        $this->foreignLink = $foreignLink;
        $this->name = $name;
    }

    public static function createBuilder(): ParamsBuilder
    {
        return new ParamsBuilder();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getForeignLink(): string
    {
        return $this->foreignLink;
    }

    public function getForeignEntityType(): ?string
    {
        return $this->foreignEntityType;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
