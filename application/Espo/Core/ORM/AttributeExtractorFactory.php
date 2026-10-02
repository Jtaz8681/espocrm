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

namespace Espo\Core\ORM;

use Espo\Core\InjectableFactory;
use Espo\Core\Utils\Metadata;

use Espo\ORM\Metadata as OrmMetadata;
use Espo\ORM\Value\AttributeExtractor;
use Espo\ORM\Value\AttributeExtractorFactory as AttributeExtractorFactoryInterface;

use RuntimeException;

/**
 * @template T of object
 * @implements AttributeExtractorFactoryInterface<T>
 */
class AttributeExtractorFactory implements AttributeExtractorFactoryInterface
{
    public function __construct(
        private Metadata $metadata,
        private OrmMetadata $ormMetadata,
        private InjectableFactory $injectableFactory
    ) {}

    /**
     * @return AttributeExtractor<T>
     */
    public function create(string $entityType, string $field): AttributeExtractor
    {
        $className = $this->getClassName($entityType, $field);

        if (!$className) {
            throw new RuntimeException("Could not get AttributeExtractor for '{$entityType}.{$field}'.");
        }

        return $this->injectableFactory->createWith($className, ['entityType' => $entityType]);
    }

    /**
     * @return ?class-string<AttributeExtractor<T>>
     */
    private function getClassName(string $entityType, string $field): ?string
    {
        $fieldDefs = $this->ormMetadata
            ->getDefs()
            ->getEntity($entityType)
            ->getField($field);

        $className = $fieldDefs->getParam('attributeExtractorClassName');

        if ($className) {
            /** @var class-string<AttributeExtractor<T>> */
            return $className;
        }

        $type = $fieldDefs->getType();

        /** @var ?class-string<AttributeExtractor<T>> */
        return $this->metadata->get(['fields', $type, 'attributeExtractorClassName']);
    }
}
