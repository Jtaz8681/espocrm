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

namespace Espo\Tools\Pdf;

use DateTime;
use Espo\Core\Acl;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\FileStorage\Manager as FileStorageManager;
use Espo\Core\Job\Job\Data as JobData;
use Espo\Core\Job\JobSchedulerFactory;
use Espo\Core\Job\QueueName;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Language;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Util;
use Espo\Entities\Attachment;
use Espo\Entities\Template as TemplateEntity;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\Tools\Pdf\Data\DataLoaderManager;
use Espo\Tools\Pdf\Jobs\RemoveMassFile;

class MassService
{
    private const DEFAULT_ENGINE = 'Dompdf';
    private const ATTACHMENT_MASS_PDF_ROLE = 'Mass Pdf';
    private const REMOVE_MASS_PDF_PERIOD = '1 hour';

    public function __construct(
        private ServiceContainer $serviceContainer,
        private Config $config,
        private EntityManager $entityManager,
        private Acl $acl,
        private DataLoaderManager $dataLoaderManager,
        private SelectBuilderFactory $selectBuilderFactory,
        private Builder $builder,
        private Language $defaultLanguage,
        private JobSchedulerFactory $jobSchedulerFactory,
        private FileStorageManager $fileStorageManager,
        private Metadata $metadata,
    ) {}

    /**
     * Generate a PDF for multiple records.
     *
     * @param string[] $idList
     * @throws Error
     * @throws NotFound
     * @throws Forbidden
     * @throws BadRequest
     */
    public function generate(
        string $entityType,
        array $idList,
        string $templateId,
        bool $withAcl = true
    ): string {

        $service = $this->serviceContainer->get($entityType);

        $maxCount = $this->config->get('massPrintPdfMaxCount');

        if ($maxCount && count($idList) > $maxCount) {
            throw new Error("Mass print to PDF max count exceeded.");
        }

        /** @var ?TemplateEntity $template */
        $template = $this->entityManager->getEntityById(TemplateEntity::ENTITY_TYPE, $templateId);

        if (!$template) {
            throw new NotFound();
        }

        $params = Params::create();

        if ($withAcl) {
            if (!$this->acl->check($template)) {
                throw new Forbidden();
            }

            if (!$this->acl->checkScope($entityType)) {
                throw new Forbidden();
            }

            $params = $params->withAcl();
        }

        $selectBuilder = $this->selectBuilderFactory
            ->create()
            ->from($entityType);

        if ($withAcl) {
            $selectBuilder->withAccessControlFilter();
        }

        $collection = $this->entityManager
            ->getRDBRepository($entityType)
            ->clone($selectBuilder->build())
            ->where([
                Attribute::ID => $idList,
            ])
            ->find();

        $idDataMap = IdDataMap::create();

        $pdfA = $this->metadata->get("pdfDefs.$entityType.pdfA") ?? false;

        $params = $params->withPdfA($pdfA);

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

        $templateWrapper = new TemplateWrapper($template);

        $engine = $this->config->get('pdfEngine') ?? self::DEFAULT_ENGINE;

        $printer = $this->builder
            ->setTemplate($templateWrapper)
            ->setEngine($engine)
            ->build();

        $contents = $printer->printCollection($collection, $params, $idDataMap);

        $entityTypeTranslated = $this->defaultLanguage->translateLabel($entityType, 'scopeNamesPlural');

        $type = $contents instanceof ZipContents ?
            'application/zip' :
            'application/pdf';

        $filename = $contents instanceof ZipContents ?
            Util::sanitizeFileName($entityTypeTranslated) . '.zip' :
            Util::sanitizeFileName($entityTypeTranslated) . '.pdf';

        /** @var Attachment $attachment */
        $attachment = $this->entityManager->getNewEntity(Attachment::ENTITY_TYPE);

        $attachment
            ->setName($filename)
            ->setType($type)
            ->setRole(self::ATTACHMENT_MASS_PDF_ROLE)
            ->setSize($contents->getStream()->getSize());

        $this->entityManager->saveEntity($attachment);

        $this->fileStorageManager->putStream($attachment, $contents->getStream());

        $this->jobSchedulerFactory
            ->create()
            ->setClassName(RemoveMassFile::class)
            ->setData(
                JobData
                    ::create()
                    ->withTargetId($attachment->getId())
                    ->withTargetType(Attachment::ENTITY_TYPE)
            )
            ->setTime(
                (new DateTime())->modify('+' . self::REMOVE_MASS_PDF_PERIOD)
            )
            ->setQueue(QueueName::Q1)
            ->schedule();

        return $attachment->getId();
    }
}
