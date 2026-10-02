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

use Espo\ORM\Entity;
use Espo\ORM\EventDispatcher;

class ValueAccessorFactory
{
    private ?GeneralValueFactory $generalValueFactory = null;
    private ?GeneralAttributeExtractor $generalAttributeExtractor = null;

    /**
     * @param AttributeExtractorFactory<object> $attributeExtractorFactory
     */
    public function __construct(
        private ValueFactoryFactory $valueFactoryFactory,
        private AttributeExtractorFactory $attributeExtractorFactory,
        private EventDispatcher $eventDispatcher
    ) {

        $this->subscribeToMetadataUpdate();
    }

    public function create(Entity $entity): ValueAccessor
    {
        return new ValueAccessor(
            $entity,
            $this->getGeneralValueFactory(),
            $this->getGeneralAttributeExtractor()
        );
    }

    private function getGeneralValueFactory(): GeneralValueFactory
    {
        if (!$this->generalValueFactory) {
            $this->generalValueFactory = new GeneralValueFactory($this->valueFactoryFactory);
        }

        return $this->generalValueFactory;
    }

    private function getGeneralAttributeExtractor(): GeneralAttributeExtractor
    {
        if (!$this->generalAttributeExtractor) {
            $this->generalAttributeExtractor = new GeneralAttributeExtractor($this->attributeExtractorFactory);
        }

        return $this->generalAttributeExtractor;
    }

    private function subscribeToMetadataUpdate(): void
    {
        $this->eventDispatcher->subscribeToMetadataUpdate(
            function () {
                $this->generalValueFactory = null;
                $this->generalAttributeExtractor = null;
            }
        );
    }
}
