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

use Espo\Core\Acl;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DateTime;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\Util;
use Espo\Entities\Template as TemplateEntity;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;
use Espo\Tools\Pdf\Data\DataLoaderManager;

class Service
{
    private const DEFAULT_ENGINE = 'Dompdf';

    public function __construct(
        private EntityManager $entityManager,
        private Acl $acl,
        private ServiceContainer $serviceContainer,
        private DataLoaderManager $dataLoaderManager,
        private Config $config,
        private Builder $builder,
        private Metadata $metadata,
        private DateTime $dateTime,
    ) {}

    /**
     * Generate a PDF.
     *
     * @param string $entityType An entity type.
     * @param string $id A record ID.
     * @param string $templateId A template ID.
     * @param ?Params $params Params. If null, a params with the apply-acl will be used.
     * @params ?Data $data Data.
     *
     * @throws Error
     * @throws NotFound
     * @throws Forbidden
     */
    public function generate(
        string $entityType,
        string $id,
        string $templateId,
        ?Params $params = null,
        ?Data $data = null,
    ): Result {

        $params = $params ?? Params::create()->withAcl(true);

        $applyAcl = $params->applyAcl();

        $entity = $this->entityManager->getEntityById($entityType, $id);

        if (!$entity) {
            throw new NotFound("Record not found.");
        }

        $template = $this->entityManager
            ->getRDBRepositoryByClass(TemplateEntity::class)
            ->getById($templateId);

        if (!$template) {
            throw new NotFound("Template not found.");
        }

        if (!$template->isActive()) {
            throw new Forbidden("Template is not active.");
        }

        if ($applyAcl && !$this->acl->checkEntityRead($entity)) {
            throw new Forbidden("No access to record.");
        }

        if ($applyAcl && !$this->acl->checkEntityRead($template)) {
            throw new Forbidden("No access to template.");
        }

        $service = $this->serviceContainer->get($entityType);

        $service->loadAdditionalFields($entity);

        if (method_exists($service, 'loadAdditionalFieldsForPdf')) {
            // For bc.
            $service->loadAdditionalFieldsForPdf($entity);
        }

        if ($template->getTargetEntityType() !== $entityType) {
            throw new Error("Not matching entity types.");
        }

        $pdfA = $this->metadata->get("pdfDefs.{$entity->getEntityType()}.pdfA") ?? false;

        $params = $params->withPdfA($pdfA);

        $data = $this->dataLoaderManager->load($entity, $params, $data);
        $engine = $this->config->get('pdfEngine') ?? self::DEFAULT_ENGINE;

        $printer = $this->builder
            ->setTemplate(new TemplateWrapper($template))
            ->setEngine($engine)
            ->build();

        $contents = $printer->printEntity($entity, $params, $data);

        $filename = $this->prepareFilename($entity, $template, $params);

        return new Result($contents, $filename);
    }

    private function prepareFilename(Entity $entity, TemplateEntity $template, Params $params): ?string
    {
        $filename = $template->getFilename() ?? null;

        if (!$filename) {
            return null;
        }

        $forbiddenAttributeList = $this->acl->getScopeForbiddenAttributeList($entity->getEntityType());

        foreach ($entity->getAttributeList() as $attribute) {
            $value = $entity->get($attribute);

            if (!is_scalar($value)) {
                continue;
            }

            if ($params->applyAcl() && in_array($attribute, $forbiddenAttributeList)) {
                continue;
            }

            $filename = str_replace('{{' . $attribute . '}}', (string) $value, $filename);
        }

        $today = $this->dateTime->getTodayString(null, DateTime::SYSTEM_DATE_FORMAT);
        $filename = str_replace('{{today}}', $today, $filename);

        if (!str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        return Util::sanitizeFileName($filename) ?: null;
    }
}
