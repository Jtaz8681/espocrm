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

namespace Espo\Tools\App;

use Espo\Core\Name\Field;
use Espo\ORM\EntityManager;

use Espo\Repositories\Preferences as Repository;
use Espo\Entities\Preferences;
use Espo\Entities\User;

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\FieldValidation\FieldValidationManager;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;

use stdClass;

class PreferencesService
{
    private EntityManager $entityManager;
    private User $user;
    private Acl $acl;
    private Config $config;
    private FieldValidationManager $fieldValidationManager;
    private Metadata $metadata;

    public function __construct(
        EntityManager $entityManager,
        User $user,
        Acl $acl,
        Config $config,
        FieldValidationManager $fieldValidationManager,
        Metadata $metadata
    ) {
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->acl = $acl;
        $this->config = $config;
        $this->fieldValidationManager = $fieldValidationManager;
        $this->metadata = $metadata;
    }

    /**
     * @throws Forbidden
     */
    protected function processAccessCheck(string $userId): void
    {
        if (!$this->user->isAdmin()) {
            if ($this->user->getId() !== $userId) {
                throw new Forbidden();
            }
        }
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function read(string $userId): Preferences
    {
        $this->processAccessCheck($userId);

        /** @var ?Preferences $entity */
        $entity = $this->entityManager->getEntityById(Preferences::ENTITY_TYPE, $userId);
        /** @var ?User $user */
        $user = $this->entityManager->getEntityById(User::ENTITY_TYPE, $userId);

        if (!$entity || !$user) {
            throw new NotFound();
        }

        $entity->set(Field::NAME, $user->getName());
        $entity->set('isPortalUser', $user->isPortal());

        // @todo Remove.
        $entity->clear('smtpPassword');

        $forbiddenAttributeList = $this->acl
            ->getScopeForbiddenAttributeList(Preferences::ENTITY_TYPE, Table::ACTION_READ);

        foreach ($forbiddenAttributeList as $attribute) {
            $entity->clear($attribute);
        }

        return $entity;
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     * @throws BadRequest
     */
    public function update(string $userId, stdClass $data): Preferences
    {
        $this->processAccessCheck($userId);

        if ($this->acl->getLevel(Preferences::ENTITY_TYPE, Table::ACTION_EDIT) === Table::LEVEL_NO) {
            throw new Forbidden();
        }

        $forbiddenAttributeList = $this->acl
            ->getScopeForbiddenAttributeList(Preferences::ENTITY_TYPE, Table::ACTION_EDIT);

        foreach ($forbiddenAttributeList as $attribute) {
            unset($data->$attribute);
        }

        /** @var ?User $user */
        $user = $this->entityManager->getEntityById(User::ENTITY_TYPE, $userId);

        /** @var ?Preferences $entity */
        $entity = $this->entityManager->getEntityById(Preferences::ENTITY_TYPE, $userId);

        if (!$entity || !$user) {
            throw new NotFound();
        }

        $entity->set($data);

        $this->fieldValidationManager->process($entity, $data);

        $this->entityManager->saveEntity($entity);

        $entity->set(Field::NAME, $user->getName());

        // @todo Remove.
        $entity->clear('smtpPassword');

        return $entity;
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function resetToDefaults(string $userId): void
    {
        $this->processAccessCheck($userId);

        $result = $this->getRepository()->resetToDefaults($userId);

        if (!$result) {
            throw new NotFound();
        }
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function resetDashboard(string $userId): stdClass
    {
        $this->processAccessCheck($userId);

        if ($this->acl->getLevel(Preferences::ENTITY_TYPE, Table::ACTION_EDIT) === Table::LEVEL_NO) {
            throw new Forbidden();
        }

        /** @var ?User $user */
        $user = $this->entityManager->getEntityById(User::ENTITY_TYPE, $userId);

        $preferences = $this->entityManager->getEntityById(Preferences::ENTITY_TYPE, $userId);

        if (!$user) {
            throw new NotFound();
        }

        if (!$preferences) {
            throw new NotFound();
        }

        if ($user->isPortal()) {
            throw new Forbidden();
        }

        $forbiddenAttributeList = $this->acl
            ->getScopeForbiddenAttributeList(Preferences::ENTITY_TYPE, Table::ACTION_EDIT);

        if (in_array('dashboardLayout', $forbiddenAttributeList)) {
            throw new Forbidden();
        }

        $dashboardLayout = $this->config->get('dashboardLayout');
        $dashletsOptions = null;

        if (!$dashboardLayout) {
            $dashboardLayout = $this->metadata->get('app.defaultDashboardLayouts.Standard');
            $dashletsOptions = $this->metadata->get('app.defaultDashboardOptions.Standard');
        }

        if ($dashletsOptions === null) {
            $dashletsOptions = $this->config->get('dashletsOptions');
        }

        $preferences->set([
            'dashboardLayout' => $dashboardLayout,
            'dashletsOptions' => $dashletsOptions,
        ]);

        $this->entityManager->saveEntity($preferences);

        return (object) [
            'dashboardLayout' => $preferences->get('dashboardLayout'),
            'dashletsOptions' => $preferences->get('dashletsOptions'),
        ];
    }

    private function getRepository(): Repository
    {
        /** @var Repository */
        return $this->entityManager->getRepository(Preferences::ENTITY_TYPE);
    }
}
