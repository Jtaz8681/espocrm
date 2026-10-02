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

namespace Espo\ORM;

use Espo\ORM\Type\AttributeType;
use Espo\ORM\Type\RelationType;
use RuntimeException;
use stdClass;

/**
 * An entity. Represents a single record in DB.
 */
interface Entity
{
    public const ID = AttributeType::ID;
    public const VARCHAR = AttributeType::VARCHAR;
    public const INT = AttributeType::INT;
    public const FLOAT = AttributeType::FLOAT;
    public const TEXT = AttributeType::TEXT;
    public const BOOL = AttributeType::BOOL;
    public const FOREIGN_ID = AttributeType::FOREIGN_ID;
    public const FOREIGN = AttributeType::FOREIGN;
    public const FOREIGN_TYPE = AttributeType::FOREIGN_TYPE;
    public const DATE = AttributeType::DATE;
    public const DATETIME = AttributeType::DATETIME;
    public const JSON_ARRAY = AttributeType::JSON_ARRAY;
    public const JSON_OBJECT = AttributeType::JSON_OBJECT;
    public const PASSWORD = AttributeType::PASSWORD;

    public const MANY_MANY = RelationType::MANY_MANY;
    public const HAS_MANY = RelationType::HAS_MANY;
    public const BELONGS_TO = RelationType::BELONGS_TO;
    public const HAS_ONE = RelationType::HAS_ONE;
    public const BELONGS_TO_PARENT = RelationType::BELONGS_TO_PARENT;
    public const HAS_CHILDREN = RelationType::HAS_CHILDREN;

    /**
     * Get an entity ID.
     *
     * @return non-empty-string
     * @throws RuntimeException If an ID is not set.
     */
    public function getId(): string;

    /**
     * Whether an ID is set.
     */
    public function hasId(): bool;

    /**
     * Reset all attributes (empty an entity).
     */
    public function reset(): void;

    /**
     * Set an attribute or multiple attributes.
     *
     * Two usage options:
     * - `set($attribute, $value)`
     * - `set($valueMap)`
     *
     * @param string|stdClass|array<string, mixed> $attribute
     * @param mixed $value
     */
    public function set($attribute, $value = null): static;

    /**
     * Set multiple attributes.
     *
     * @param array<string, mixed>|stdClass $valueMap Values.
     * @since v8.1.0.
     */
    public function setMultiple(array|stdClass $valueMap): static;

    /**
     * Get an attribute value.
     *
     * @return mixed
     */
    public function get(string $attribute);

    /**
     * Whether an attribute value is set.
     */
    public function has(string $attribute): bool;

    /**
     * Clear an attribute value.
     */
    public function clear(string $attribute): void;

    /**
     * Get an entity type.
     */
    public function getEntityType(): string;

    /**
     * Get attribute list defined for an entity type.
     *
     * @return string[]
     */
    public function getAttributeList(): array;

    /**
     * Get relation list defined for an entity type.
     *
     * @return string[]
     */
    public function getRelationList(): array;

    /**
     * Whether an entity type has an attribute defined.
     */
    public function hasAttribute(string $attribute): bool;

    /**
     * Whether an entity type has a relation defined.
     */
    public function hasRelation(string $relation): bool;

    /**
     * Get an attribute type.
     */
    public function getAttributeType(string $attribute): ?string;

    /**
     * Get a relation type.
     */
    public function getRelationType(string $relation): ?string;

    /**
     * Whether an entity is new.
     */
    public function isNew(): bool;

    /**
     * Set an entity as fetched. All current attribute values will be set as those that are fetched
     * from the database.
     */
    public function setAsFetched(): void;

    /**
     * Whether is fetched from the database.
     */
    public function isFetched(): bool;

    /**
     * Whether an attribute was changed (since syncing with the database).
     */
    public function isAttributeChanged(string $name): bool;

    /**
     * Get a fetched value of a specific attribute.
     *
     * @return mixed
     */
    public function getFetched(string $attribute);

    /**
     * Whether a fetched value is set for a specific attribute.
     */
    public function hasFetched(string $attribute): bool;

    /**
     * Set a fetched value for a specific attribute.
     *
     * @param mixed $value
     */
    public function setFetched(string $attribute, $value): static;

    /**
     * Get values.
     */
    public function getValueMap(): stdClass;

    /**
     * Set as not new. Meaning the entity is fetched or already saved.
     */
    public function setAsNotNew(): void;

    /**
     * Copy all current values to fetched values. All current attribute values will be set as those
     * that are fetched from DB.
     */
    public function updateFetchedValues(): void;
}
