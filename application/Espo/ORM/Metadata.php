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

use Espo\ORM\Defs\DefsData;

use InvalidArgumentException;

/**
 * Metadata.
 */
class Metadata
{
    /** @var array<string, mixed> */
    private array $data;

    private Defs $defs;
    private DefsData $defsData;
    private EventDispatcher $eventDispatcher;

    public function __construct(
        private MetadataDataProvider $dataProvider,
        ?EventDispatcher $eventDispatcher = null
    ) {
        $this->data = $dataProvider->get();
        $this->defsData = new DefsData($this);
        $this->defs = new Defs($this->defsData);
        $this->eventDispatcher = $eventDispatcher ?? new EventDispatcher();
    }

    /**
     * Update data from the data provider.
     */
    public function updateData(): void
    {
        $this->data = $this->dataProvider->get();

        $this->defsData->clearCache();

        $this->eventDispatcher->dispatchMetadataUpdate();
    }

    /**
     * Get definitions.
     */
    public function getDefs(): Defs
    {
        return $this->defs;
    }

    /**
     * Get a parameter or parameters by key. Key can be a string or array path.
     *
     * @param string $entityType An entity type.
     * @param string[]|string|null $key A Key.
     * @param mixed $default A default value.
     * @return mixed
     */
    public function get(string $entityType, $key = null, $default = null)
    {
        if (!$this->has($entityType)) {
            return null;
        }

        $data = $this->data[$entityType];

        if ($key === null) {
            return $data;
        }

        return self::getValueByKey($data, $key, $default);
    }

    /**
     * Whether an entity type is available.
     */
    public function has(string $entityType): bool
    {
        return array_key_exists($entityType, $this->data);
    }

    /**
     * Get a list of entity types.
     *
     * @return string[]
     */
    public function getEntityTypeList(): array
    {
        return array_keys($this->data);
    }

    /**
     * @param array<string, mixed> $data
     * @param string[]|string|null $key
     * @param mixed $default A default value.
     * @return mixed
     */
    private static function getValueByKey(array $data, $key = null, $default = null)
    {
        if (!is_string($key) && !is_array($key) && !is_null($key)) { /** @phpstan-ignore-line */
            throw new InvalidArgumentException();
        }

        if (is_null($key) || empty($key)) {
            return $data;
        }

        $path = $key;

        if (is_string($key)) {
            $path = explode('.', $key);
        }

        /** @var string[] $path */

        $item = $data;

        foreach ($path as $k) {
            if (!array_key_exists($k, $item)) {
                return $default;
            }

            $item = $item[$k];
        }

        return $item;
    }
}
