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

namespace tests\integration\Core;

use Espo\Core\Application;
use Espo\Core\Container;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\Core\Utils\PasswordHash;
use Espo\Entities\Preferences;
use Espo\Entities\User;
use Espo\ORM\EntityManager;
use Exception;
use RuntimeException;

class DataLoader
{
    private Application $application;
    private PasswordHash $passwordHash;

    public function __construct(Application $application)
    {
        $this->application = $application;

        $config = $this->getContainer()->getByClass(Config::class);

        $this->passwordHash = new PasswordHash($config);
    }

    private function getContainer(): Container
    {
        return $this->application->getContainer();
    }

    private function getPasswordHash(): PasswordHash
    {
        return $this->passwordHash;
    }

    public function loadData(string $dataFile): void
    {
        if (!file_exists($dataFile)) {
            return;
        }

        $data = include($dataFile);

        $this->handleData($data);
    }

    public function setData(array $data): void
    {
        $this->handleData($data);
    }

    protected function handleData(array $fullData): void
    {
        foreach ($fullData as $type => $data) {
            if ($type === 'files') {
                $this->loadFiles($data);

                continue;
            }

            if ($type === 'entities') {
                $this->loadEntities($data);

                continue;
            }

            if ($type === 'data') {
                $this->loadData($data);

                continue;
            }

            if ($type === 'config') {
                $this->loadConfig($data);

                continue;
            }

            if ($type === 'preferences') {
                $this->loadPreferences($data);

                continue;
            }


            throw new RuntimeException('DataLoader: Data type is not supported in dataFile.');
        }
    }

    public function loadFiles(string $path): void
    {
        try {
            $fileManager = $this->getContainer()->getByClass(FileManager::class);

            $fileManager->copy($path, '.', true);
        } catch (Exception $e) {
            throw new RuntimeException('Error loadFiles: ' . $e->getMessage());
        }
    }

    protected function loadEntities(array $data)
    {
        $entityManager = $this->getContainer()->getByClass(EntityManager::class);

        foreach ($data as $entityType => $entities) {
            foreach($entities as $entityData) {
                $entity = $entityManager->getEntityById($entityType, $entityData['id']);

                if (empty($entity)) {
                    $entity = $entityManager->getNewEntity($entityType);
                }

                foreach($entityData as $field => $value) {
                    if ($field == 'password' && $entityType == User::ENTITY_TYPE) {
                        $value = $this->getPasswordHash()->hash($value);
                    }

                    $entity->set($field, $value);
                }

                try {
                    $entityManager->saveEntity($entity);
                } catch (Exception $e) {
                    throw new RuntimeException('Save entity error.', previous: $e);
                }
            }
        }
    }

    private function loadConfig(array $data): void
    {
        if (empty($data)) {
            return;
        }

        $config = $this->getContainer()->getByClass(Config::class);
        $config->set($data);

        try {
            $config->save();
        } catch (Exception $e) {
            throw new RuntimeException('Error loadConfig: ' . $e->getMessage());
        }
    }

    private function loadPreferences(array $data): void
    {
        $entityManager = $this->getContainer()->getByClass(EntityManager::class);

        foreach ($data as $userId => $params) {
            $entityManager->getRepository(Preferences::ENTITY_TYPE)->resetToDefaults($userId);

            $preferences = $entityManager->getEntityById(Preferences::ENTITY_TYPE, $userId);
            $preferences->set($params);

            try {
                $entityManager->saveEntity($preferences);
            } catch (Exception $e) {
                throw new RuntimeException('Error loadPreferences: ' . $e->getMessage());
            }
        }
    }
}
