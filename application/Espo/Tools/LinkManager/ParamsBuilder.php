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

class ParamsBuilder
{
    private string $type;
    private string $entityType;
    private string $link;
    private string $foreignLink;
    private ?string $foreignEntityType = null;
    private ?string $name = null;

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function setEntityType(string $entityType): self
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function setForeignLink(string $foreignLink): self
    {
        $this->foreignLink = $foreignLink;

        return $this;
    }

    public function setForeignEntityType(?string $foreignEntityType): self
    {
        $this->foreignEntityType = $foreignEntityType;

        return $this;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function build(): Params
    {
        return new Params(
            $this->type,
            $this->entityType,
            $this->link,
            $this->foreignEntityType,
            $this->foreignLink,
            $this->name
        );
    }
}
