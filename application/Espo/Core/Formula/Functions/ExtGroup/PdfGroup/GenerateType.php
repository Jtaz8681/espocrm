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

namespace Espo\Core\Formula\Functions\ExtGroup\PdfGroup;

use Espo\Core\Field\LinkParent;
use Espo\Core\Formula\Exceptions\FunctionRuntimeError;
use Espo\Core\Name\Field;
use Espo\Entities\Attachment;
use Espo\Entities\Template;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Utils\Util;
use Espo\ORM\Entity;
use Espo\Tools\Pdf\Params;
use Espo\Core\Di;
use Espo\Tools\Pdf\Result;
use Espo\Tools\Pdf\Service;
use Exception;

/**
 * @noinspection PhpUnused
 */
class GenerateType extends BaseFunction implements
    Di\EntityManagerAware,
    Di\InjectableFactoryAware,
    Di\FileStorageManagerAware
{
    use Di\EntityManagerSetter;
    use Di\InjectableFactorySetter;
    use Di\FileStorageManagerSetter;

    public function process(ArgumentList $args)
    {
        if (count($args) < 3) {
            $this->throwTooFewArguments(3);
        }

        $args = $this->evaluate($args);

        $entityType = $args[0];
        $id = $args[1];
        $templateId = $args[2];
        $fileName = $args[3] ?? null;

        if (!$entityType || !is_string($entityType)) {
            $this->throwBadArgumentType(1, 'string');
        }

        if (!$id || !is_string($id)) {
            $this->throwBadArgumentType(2, 'string');
        }

        if (!$templateId || !is_string($templateId)) {
            $this->throwBadArgumentType(3, 'string');
        }

        if ($fileName && !is_string($fileName)) {
            $this->throwBadArgumentType(4, 'string');
        }

        $em = $this->entityManager;

        $entity = $em->getEntityById($entityType, $id);

        if (!$entity) {
            throw new FunctionRuntimeError("Record $entityType $id does not exist.");
        }

        $template = $em->getRDBRepositoryByClass(Template::class)->getById($templateId);

        if (!$template) {
            throw new FunctionRuntimeError("Template $templateId does not exist.");
        }

        $params = Params::create()->withAcl(false);

        $service = $this->injectableFactory->create(Service::class);

        try {
            $result = $service->generate(
                entityType: $entity->getEntityType(),
                id: $entity->getId(),
                templateId: $template->getId(),
                params: $params,
            );
        } catch (Exception $e) {
            throw new FunctionRuntimeError("Error while generating PDF template. {$e->getMessage()}", previous: $e);
        }

        $fileName = $this->prepareFilename($fileName, $result, $entity);

        $attachment = $em->getRDBRepositoryByClass(Attachment::class)->getNew();

        $attachment
            ->setName($fileName)
            ->setType('application/pdf')
            ->setSize($result->getStream()->getSize())
            ->setRelated(LinkParent::create($entityType, $id))
            ->setRole(Attachment::ROLE_ATTACHMENT);

        $em->saveEntity($attachment);

        $this->fileStorageManager->putStream($attachment, $result->getStream());

        return $attachment->getId();
    }

    private function composeFilename(Entity $entity): string
    {
        $defaultName = $entity->get(Field::NAME) ?? $entity->getId();

        return Util::sanitizeFileName($defaultName) . '.pdf';
    }

    private function prepareFilename(mixed $fileName, Result $result, Entity $entity): string
    {
        if ($fileName) {
            if (!str_ends_with($fileName, '.pdf')) {
                $fileName .= '.pdf';
            }

            return $fileName;
        }

        return $result->getFilename() ?? $this->composeFilename($entity);
    }
}
