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

use Espo\Core\Container;
use Espo\Entities\EmailAccount;
use Espo\Entities\User;
use Espo\Entities\Preferences;
use Espo\ORM\EntityManager;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Database\Helper as DatabaseHelper;

class BeforeUpgrade
{
    public function run(Container $container): void
    {
        $this->addUserColumn($container->get('config'));
        $this->migrateSmtp($container->get('entityManager'));
    }

    private function migrateSmtp(EntityManager $entityManager): void
    {
        $users = $entityManager
            ->getRDBRepositoryByClass(User::class)
            ->where([
                'isActive' => true,
                'type' => [
                    User::TYPE_REGULAR,
                    User::TYPE_ADMIN,
                ]
            ])
            ->find();

        foreach ($users as $user) {
            $this->migrateSmtpForUser($entityManager, $user);
        }
    }

    private function migrateSmtpForUser(EntityManager $entityManager, User $user): void
    {
        $preferences = $entityManager->getEntityById(Preferences::ENTITY_TYPE, $user->getId());

        if (!$preferences) {
            return;
        }

        $emailAddress = $user->getEmailAddress();
        $smtpServer = $preferences->get('smtpServer');

        if (!$smtpServer) {
            return;
        }

        if (!$emailAddress) {
            return;
        }

        $existingAccount = $entityManager
            ->getRDBRepositoryByClass(EmailAccount::class)
            ->where([
                'assignedUserId' => $user->getId(),
                'emailAddress' => $emailAddress,
                'useSmtp' => true,
                'status' => 'Active',
            ])
            ->findOne();

        if ($existingAccount) {
            return;
        }

        $account = $entityManager
            ->getRDBRepositoryByClass(EmailAccount::class)
            ->getNew();

        $account->set([
            'assignedUserId' => $user->getId(),
            'name' => $emailAddress . ' (auto-created)',
            'emailAddress' => $emailAddress,
            'useImap' => false,
            'useSmtp' => true,
            'status' => 'Active',
            'smtpHost' => $smtpServer,
            'smtpPort' => $preferences->get('smtpPort'),
            'smtpAuth' => $preferences->get('smtpAuth'),
            'smtpSecurity' => $preferences->get('smtpSecurity'),
            'smtpUsername' => $preferences->get('smtpUsername'),
            'smtpPassword' => $preferences->get('smtpPassword'),
        ]);

        $entityManager->saveEntity($account);

        $preferences->set('smtpServer', null);
        $preferences->set('smtpPort', null);
        $preferences->set('smtpAuth', null);
        $preferences->set('smtpAuth', null);
        $preferences->set('smtpSecurity', null);
        $preferences->set('smtpUsername', null);
        $preferences->set('smtpPassword', null);

        $entityManager->saveEntity($preferences);
    }

    private function addUserColumn(Config $config)
    {
        $databaseHelper = new DatabaseHelper($config);

        $pdo = $databaseHelper->getDatabaseType();

        $query = "
            ALTER TABLE `user` ADD `working_time_calendar_id` VARCHAR(24)
            DEFAULT NULL COLLATE `utf8mb4_unicode_ci`
        ";

        try {
            $sth = $pdo->prepare($query);
            $sth->execute();
        } catch (\Exception $e) {}
    }
}
