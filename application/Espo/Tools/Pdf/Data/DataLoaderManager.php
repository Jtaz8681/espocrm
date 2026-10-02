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

namespace Espo\Tools\Pdf\Data;

use Espo\ORM\Entity;
use Espo\Core\Utils\Metadata;
use Espo\Core\InjectableFactory;
use Espo\Tools\Pdf\AttachmentProvider;
use Espo\Tools\Pdf\Data;
use Espo\Tools\Pdf\Params;

class DataLoaderManager
{
    public function __construct(
        private Metadata $metadata,
        private InjectableFactory $injectableFactory,
    ) {}

    public function load(Entity $entity, ?Params $params = null, ?Data $data = null): Data
    {
        if (!$params) {
            $params = Params::create();
        }

        if (!$data) {
            $data = Data::create();
        }

        $defs = $this->metadata->get("pdfDefs.{$entity->getEntityType()}") ?? [];

        /** @var class-string<DataLoader>[] $loaderClassList */
        $loaderClassList = $defs['dataLoaderClassNameList'] ?? [];

        foreach ($loaderClassList as $className) {
            $loadedData = $this->createLoader($className)
                ->load($entity, $params);

            $data = $data->withAdditionalTemplateData($loadedData);
        }

        /** @var class-string<AttachmentProvider<Entity>>[] $attachmentProviderClassList */
        $attachmentProviderClassList = $defs['attachmentProviderClassNameList'] ?? [];

        foreach ($attachmentProviderClassList as $className) {
            $provider = $this->createProvider($className);

            $attachments = $provider->get($entity, $params);

            $data = $data->withAttachmentsAdded($attachments);
        }

        return $data;
    }

    /**
     * @param class-string<DataLoader> $className
     */
    private function createLoader(string $className): DataLoader
    {
        return $this->injectableFactory->create($className);
    }

    /**
     * @param class-string<AttachmentProvider<Entity>> $className
     * @return AttachmentProvider<Entity>
     */
    private function createProvider(string $className): AttachmentProvider
    {
        /** @var AttachmentProvider<Entity> */
        return $this->injectableFactory->create($className);
    }
}
