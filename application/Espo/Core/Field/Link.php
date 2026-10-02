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

namespace Espo\Core\Field;

use Espo\ORM\Entity;
use InvalidArgumentException;

/**
 * A link value object. Immutable.
 */
class Link
{
    private string $id;
    private ?string $name = null;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $id)
    {
        if (!$id) {
            throw new InvalidArgumentException("Empty ID.");
        }

        $this->id = $id;
    }

    /**
     * Get an ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get a name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Clone with a name.
     */
    public function withName(?string $name): self
    {
        $obj = new self($this->id);

        $obj->name = $name;

        return $obj;
    }

    /**
     * Create from an ID.
     *
     * @throws InvalidArgumentException
     */
    public static function create(string $id, ?string $name = null): self
    {
        return (new self($id))->withName($name);
    }

    /**
     * Create from an entity.
     *
     * @throws InvalidArgumentException
     *
     * @since 10.0.0
     */
    public static function fromEntity(Entity $entity): self
    {
        return self::create($entity->getId());
    }
}
