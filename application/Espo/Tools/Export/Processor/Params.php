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

namespace Espo\Tools\Export\Processor;

use RuntimeException;

/**
 * Immutable.
 */
class Params
{
    private string $fileName;
    /** @var string[] */
    private array $attributeList;
    /** @var ?string[] */
    private ?array $fieldList = null;
    private ?string $name = null;
    private ?string $entityType = null;
    /** @var array<string, mixed> */
    private array $params = [];

    /**
     * @param string[] $attributeList
     * @param ?string[] $fieldList
     */
    public function __construct(string $fileName, array $attributeList, ?array $fieldList)
    {
        $this->fileName = $fileName;
        $this->attributeList = $attributeList;
        $this->fieldList = $fieldList;
    }

    public function withEntityType(string $entityType): self
    {
        $obj = clone $this;
        $obj->entityType = $entityType;

        return $obj;
    }

    public function withName(?string $name): self
    {
        $obj = clone $this;
        $obj->name = $name;

        return $obj;
    }

    /**
     * @param ?string[] $fieldList
     */
    public function withFieldList(?array $fieldList): self
    {
        $obj = clone $this;
        $obj->fieldList = $fieldList;

        return $obj;
    }

    /**
     * @param string[] $attributeList
     */
    public function withAttributeList(array $attributeList): self
    {
        $obj = clone $this;
        $obj->attributeList = $attributeList;

        return $obj;
    }

    public function withParam(string $name, mixed $value): self
    {
        $obj = clone $this;
        $obj->params[$name] = $value;

        return $obj;
    }

    /**
     * An export file name.
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Attributes to export.
     *
     * @return string[]
     */
    public function getAttributeList(): array
    {
        return $this->attributeList;
    }

    /**
     * Fields to export.
     *
     * @return ?string[]
     */
    public function getFieldList(): ?array
    {
        return $this->fieldList;
    }

    /**
     * An export name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * An entity type.
     */
    public function getEntityType(): string
    {
        if ($this->entityType === null) {
            throw new RuntimeException("No entity-type.");
        }

        return $this->entityType;
    }

    /**
     * Get a parameter value.
     */
    public function getParam(string $name): mixed
    {
        return $this->params[$name] ?? null;
    }
}
