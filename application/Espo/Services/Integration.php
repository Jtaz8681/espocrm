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

namespace Espo\Services;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Config\ConfigWriter;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Integration as IntegrationEntity;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

use stdClass;

class Integration
{
    public function __construct(
        private EntityManager $entityManager,
        private User $user,
        private Config $config,
        private ConfigWriter $configWriter,
        private Metadata $metadata,
    ) {}

    /**
     * @return void
     * @throws Forbidden
     */
    protected function processAccessCheck()
    {
        if (!$this->user->isAdmin()) {
            throw new Forbidden();
        }
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function read(string $id): Entity
    {
        $this->processAccessCheck();

        /** @var ?IntegrationEntity $entity */
        $entity = $this->entityManager->getEntityById(IntegrationEntity::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFound();
        }

        $this->prepareEntity($entity);

        return $entity;
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function update(string $id, stdClass $data): Entity
    {
        $this->processAccessCheck();

        /** @var ?IntegrationEntity $entity */
        $entity = $this->entityManager->getEntityById(IntegrationEntity::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFound();
        }

        $entity->set($data);

        $this->entityManager->saveEntity($entity);

        $configData = $this->config->get('integrations') ?? (object) [];

        if (!$configData instanceof stdClass) {
            $configData = (object) [];
        }

        $configData->$id = $entity->get('enabled');

        $this->configWriter->set('integrations', $configData);
        $this->configWriter->save();

        $this->prepareEntity($entity);

        return $entity;
    }

    private function prepareEntity(IntegrationEntity $entity): void
    {
        /** @var array<string, array<string, mixed>> $fields */
        $fields = $this->metadata->get("integrations.{$entity->getId()}.fields") ?? [];

        foreach ($fields as $field => $fieldDefs) {
            $type = $fieldDefs['type'] ?? null;

            if ($type === FieldType::PASSWORD) {
                $entity->clear($field);
            }

            // @todo Clear readOnly.
        }
    }
}
