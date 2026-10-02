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
use Espo\ORM\EntityManager;

/** @noinspection PhpMultipleClassDeclarationsInspection */
class BeforeUpgrade
{
    private ?Container $container = null;

    /**
     * @throws Exception
     */
    public function run(Container $container): void
    {
        $this->container = $container;
        $this->processCheckExtensions();

        $this->checkRepositories($container->getByClass(EntityManager::class));
    }

    /**
     * @throws Exception
     */
    private function checkRepositories(EntityManager $em): void
    {
        /** @var class-string[] $classNameList */
        $classNameList = [
            "Espo\\Repositories\\ActionHistoryRecord",
            "Espo\\Repositories\\NextNumber",
            "Espo\\Repositories\\AuthLogRecord",
            "Espo\\Repositories\\AuthToken",
            "Espo\\Modules\\Crm\\Repositories\\Account",
            "Espo\\Modules\\Crm\\Repositories\\Call",
            "Espo\\Modules\\Crm\\Repositories\\CaseObj",
            "Espo\\Modules\\Crm\\Repositories\\Contact",
            "Espo\\Modules\\Crm\\Repositories\\KnowledgeBaseArticle",
            "Espo\\Modules\\Crm\\Repositories\\Lead",
            "Espo\\Modules\\Crm\\Repositories\\Meeting",
            "Espo\\Modules\\Crm\\Repositories\\Opportunity",
            "Espo\\Modules\\Crm\\Repositories\\TargetList",
            "Espo\\Modules\\Crm\\Repositories\\Task",
        ];

        $list = [];

        foreach ($em->getMetadata()->getEntityTypeList() as $entityType) {
            if (!$em->hasRepository($entityType)) {
                continue;
            }

            $repository = $em->getRepository($entityType);

            if (in_array(get_class($repository), $classNameList)) {
                continue;
            }

            foreach ($classNameList as $className) {
                if (is_a($repository, $className)) {
                    $list[] = get_class($repository);
                }
            }
        }

        if ($list === []) {
            return;
        }

        $msg = implode(', ', $list) .
            " should extend from Espo\\Core\\Repositories\\Database. Fix before upgrading.";

        throw new Exception($msg);
    }

    /**
     * @throws Error
     */
    private function processCheckExtensions(): void
    {
        $errorMessageList = [];

        $this->processCheckExtension('Advanced Pack', '3.1.0', $errorMessageList);
        $this->processCheckExtension('Real Estate', '1.8.0', $errorMessageList);

        if (!count($errorMessageList)) {
            return;
        }

        $message = implode("\n\n", $errorMessageList);

        throw new Error($message);
    }

    private function processCheckExtension(string $name, string $minVersion, array &$errorMessageList): void
    {
        $em = $this->container->get('entityManager');

        $extension = $em->getRDBRepository('Extension')
            ->where([
                'name' => $name,
                'isInstalled' => true,
            ])
            ->findOne();

        if (!$extension) {
            return;
        }

        $version = $extension->get('version');

        if (version_compare($version, $minVersion, '>=')) {
            return;
        }

        $message =
            "EspoCRM 8.2 is not compatible with '$name' extension of versions lower than $minVersion. " .
            "Please upgrade the extension or uninstall it.";

        $errorMessageList[] = $message;
    }
}
