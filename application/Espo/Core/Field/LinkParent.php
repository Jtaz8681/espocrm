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
 * A link-parent value object. Immutable.
 */
class LinkParent
{
    private string $entityType;
    private string $id;
    private ?string $name = null;

    public function __construct(string $entityType, string $id)
    {
        if (!$entityType) {
            throw new InvalidArgumentException("Empty entity type.");
        }

        if (!$id) {
            throw new InvalidArgumentException("Empty ID.");
        }

        $this->entityType = $entityType;
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
     * Get an entity type.
     */
    public function getEntityType(): string
    {
        return $this->entityType;
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
        $obj = new self($this->entityType, $this->id);

        $obj->name = $name;

        return $obj;
    }

    /**
     * Create.
     *
     * @throws InvalidArgumentException
     */
    public static function create(string $entityType, string $id): self
    {
        return new self($entityType, $id);
    }

    /**
     * Create from an entity.
     *
     * @deprecated Since v10.0.0. Use `fromEntity`.
     * @todo Remove in v12.0.
     */
    public static function createFromEntity(Entity $entity): self
    {
        return new self($entity->getEntityType(), $entity->getId());
    }

    /**
     * Create from an entity.
     *
     * @since 10.0.0
     */
    public static function fromEntity(Entity $entity): self
    {
        return new self($entity->getEntityType(), $entity->getId());
    }
}
