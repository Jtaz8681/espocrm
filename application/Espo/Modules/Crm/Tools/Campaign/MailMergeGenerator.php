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

namespace Espo\Modules\Crm\Tools\Campaign;

use Espo\Core\Exceptions\Error;
use Espo\Core\Field\LinkParent;
use Espo\Core\FileStorage\Manager as FileStorageManager;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Util;
use Espo\Entities\Attachment;
use Espo\Entities\Template;
use Espo\Modules\Crm\Entities\Campaign;
use Espo\ORM\Entity;
use Espo\ORM\EntityCollection;
use Espo\ORM\EntityManager;
use Espo\Tools\Pdf\Builder;
use Espo\Tools\Pdf\Data\DataLoaderManager;
use Espo\Tools\Pdf\IdDataMap;
use Espo\Tools\Pdf\Params;
use Espo\Tools\Pdf\TemplateWrapper;
use Espo\Tools\Pdf\ZipContents;

class MailMergeGenerator
{
    private const DEFAULT_ENGINE = 'Dompdf';
    private const ATTACHMENT_MAIL_MERGE_ROLE = 'Mail Merge';

    private EntityManager $entityManager;
    private DataLoaderManager $dataLoaderManager;
    private ServiceContainer $serviceContainer;
    private Builder $builder;
    private Config $config;
    private FileStorageManager $fileStorageManager;

    public function __construct(
        EntityManager $entityManager,
        DataLoaderManager $dataLoaderManager,
        ServiceContainer $serviceContainer,
        Builder $builder,
        Config $config,
        FileStorageManager $fileStorageManager
    ) {
        $this->entityManager = $entityManager;
        $this->dataLoaderManager = $dataLoaderManager;
        $this->serviceContainer = $serviceContainer;
        $this->builder = $builder;
        $this->config = $config;
        $this->fileStorageManager = $fileStorageManager;
    }

    /**
     * Generate a mail-merge PDF.
     *
     * @return string An attachment ID.
     * @param EntityCollection<Entity> $collection
     * @throws Error
     */
    public function generate(
        EntityCollection $collection,
        Template $template,
        ?string $campaignId = null,
        ?string $name = null
    ): string {

        $entityType = $collection->getEntityType();

        if (!$entityType) {
            throw new Error("No entity type.");
        }

        $name = $name ?? $campaignId ?? $entityType;

        $params = Params::create()->withAcl();

        $idDataMap = IdDataMap::create();

        $service = $this->serviceContainer->get($entityType);

        foreach ($collection as $entity) {
            $service->loadAdditionalFields($entity);

            $idDataMap->set(
                $entity->getId(),
                $this->dataLoaderManager->load($entity, $params)
            );

            // For bc.
            if (method_exists($service, 'loadAdditionalFieldsForPdf')) {
                $service->loadAdditionalFieldsForPdf($entity);
            }
        }

        $engine = $this->config->get('pdfEngine') ?? self::DEFAULT_ENGINE;

        $templateWrapper = new TemplateWrapper($template);

        $printer = $this->builder
            ->setTemplate($templateWrapper)
            ->setEngine($engine)
            ->build();

        $contents = $printer->printCollection($collection, $params, $idDataMap);

        $type = $contents instanceof ZipContents ?
            'application/zip' :
            'application/pdf';

        $filename = $contents instanceof ZipContents ?
            Util::sanitizeFileName($name) . '.zip' :
            Util::sanitizeFileName($name) . '.pdf';

        /** @var Attachment $attachment */
        $attachment = $this->entityManager->getNewEntity(Attachment::ENTITY_TYPE);

        $relatedLink = $campaignId ?
            LinkParent::create(Campaign::ENTITY_TYPE, $campaignId) : null;

        $attachment
            ->setRelated($relatedLink)
            ->setSize($contents->getStream()->getSize())
            ->setRole(self::ATTACHMENT_MAIL_MERGE_ROLE)
            ->setName($filename)
            ->setType($type);

        $this->entityManager->saveEntity($attachment);

        $this->fileStorageManager->putStream($attachment, $contents->getStream());

        return $attachment->getId();
    }
}
