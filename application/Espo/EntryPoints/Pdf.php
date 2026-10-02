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

namespace Espo\EntryPoints;

use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\EntryPoint\EntryPoint;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Name\Field;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Util;
use Espo\Entities\Template;
use Espo\ORM\Entity;
use Espo\Tools\Pdf\Service;

class Pdf implements EntryPoint
{
    public function __construct(
        private EntityManager $entityManager,
        private Service $service,
    ) {}

    public function run(Request $request, Response $response): void
    {
        $entityId = $request->getQueryParam('entityId');
        $entityType = $request->getQueryParam('entityType');
        $templateId = $request->getQueryParam('templateId');

        if (!$entityId || !$entityType || !$templateId) {
            throw new BadRequest("No entityId or entityType or templateId.");
        }

        $entity = $this->entityManager->getEntityById($entityType, $entityId);
        /** @var ?Template $template */
        $template = $this->entityManager->getEntityById(Template::ENTITY_TYPE, $templateId);

        if (!$entity) {
            throw new NotFound("Record not found.");
        }

        if (!$template) {
            throw new NotFound("Template not found.");
        }

        $result = $this->service->generate($entityType, $entityId, $templateId);

        $fileName = $result->getFilename() ?? $this->composeFileName($entity);
        $fileName = Util::sanitizeFileName($fileName);

        $response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Cache-Control', 'private, must-revalidate, post-check=0, pre-check=0, max-age=1')
            ->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT')
            ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"');

        if (!$request->getServerParam('HTTP_ACCEPT_ENCODING')) {
            $response->setHeader('Content-Length', (string) $result->getStream()->getSize());
        }

        $response->writeBody($result->getStream());
    }

    private function composeFileName(Entity $entity): string
    {
        $name = $entity->get(Field::NAME) ?? $entity->getId();

        return $name . '.pdf';
    }
}
