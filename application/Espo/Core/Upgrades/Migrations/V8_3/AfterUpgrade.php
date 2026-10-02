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

namespace Espo\Core\Upgrades\Migrations\V8_3;

use Doctrine\DBAL\Exception as DbalException;
use Espo\Core\Templates\Entities\Event;
use Espo\Core\Upgrades\Migration\Script;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Database\Helper;
use Espo\Core\Utils\Metadata;
use Espo\Entities\AuthenticationProvider;
use Espo\Entities\Role;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\UpdateBuilder;

class AfterUpgrade implements Script
{
    public function __construct(
        private EntityManager $entityManager,
        private Metadata $metadata,
        private Config $config,
        private Helper $helper
    ) {}

    /**
     * @throws DbalException
     */
    public function run(): void
    {
        $this->updateRoles();
        $this->updateMetadata();
        $this->updateAuthenticationProviders();
        $this->renameSubscription();
    }

    private function updateRoles(): void
    {
        $query = UpdateBuilder::create()
            ->in(Role::ENTITY_TYPE)
            ->set(['mentionPermission' => Expression::column('assignmentPermission')])
            ->build();

        $this->entityManager->getQueryExecutor()->execute($query);
    }

    private function updateMetadata(): void
    {
        $defs = $this->metadata->get(['scopes']);

        foreach ($defs as $entityType => $item) {
            $isCustom = $item['isCustom'] ?? false;
            $type = $item['type'] ?? false;

            if (!$isCustom) {
                continue;
            }

            if ($type !== Event::TEMPLATE_TYPE) {
                continue;
            }

            $clientDefs = $this->metadata->getCustom('clientDefs', $entityType) ?? (object) [];

            $clientDefs->viewSetupHandlers ??= (object) [];

            $clientDefs->viewSetupHandlers->{'record/detail'} = [
                "__APPEND__",
                "crm:handlers/event/reminders-handler"
            ];

            $clientDefs->viewSetupHandlers->{'record/edit'} = [
                "__APPEND__",
                "crm:handlers/event/reminders-handler"
            ];

            if (isset($clientDefs->dynamicLogic->fields->reminders)) {
                unset($clientDefs->dynamicLogic->fields->reminders);
            }

            $this->metadata->saveCustom('clientDefs', $entityType, $clientDefs);
        }
    }

    private function updateAuthenticationProviders(): void
    {
        $collection = $this->entityManager->getRDBRepositoryByClass(AuthenticationProvider::class)
            ->where(['method' => 'Oidc'])
            ->find();

        foreach ($collection as $entity) {
            $entity->set('oidcAuthorizationPrompt', $this->config->get('oidcAuthorizationPrompt'));

            $this->entityManager->saveEntity($entity);
        }
    }

    /**
     * @throws DbalException
     */
    private function renameSubscription(): void
    {
        $connection = $this->helper->getDbalConnection();
        $schemaManager = $connection->createSchemaManager();

        if (!$schemaManager->tablesExist('subscription')) {
            return;
        }

        if ($schemaManager->tablesExist('stream_subscription')) {
            try {
                $schemaManager->dropTable('stream_subscription');
            } catch (DbalException) {
                $schemaManager->renameTable('stream_subscription', 'stream_subscription_waste');
            }
        }

        $schemaManager->renameTable('subscription', 'stream_subscription');
    }
}
