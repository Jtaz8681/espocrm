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

namespace Espo\ORM\Value;

use stdClass;

class GeneralAttributeExtractor
{
    /** @var AttributeExtractorFactory<object> */
    private AttributeExtractorFactory $factory;

    /**
     * @var array<string, AttributeExtractor<object>>
     */
    private $cache = [];

    /**
     * @param AttributeExtractorFactory<object> $factory
     */
    public function __construct(AttributeExtractorFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Extracts attributes from a value object.
     */
    public function extract(string $entityType, string $field, ?object $value): stdClass
    {
        $extractor = $this->getExtractor($entityType, $field);

        if (is_null($value)) {
            return $extractor->extractFromNull($field);
        }

        return $extractor->extract($value, $field);
    }

    /**
     * @return AttributeExtractor<object>
     */
    private function getExtractor(string $entityType, string $field): AttributeExtractor
    {
        $key = $entityType . '_' . $field;

        if (!array_key_exists($key, $this->cache)) {
            $this->cache[$key] = $this->factory->create($entityType, $field);
        }

        return $this->cache[$key];
    }
}
