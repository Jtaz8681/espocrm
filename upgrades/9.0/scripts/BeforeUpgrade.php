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
use Espo\Entities\Extension;
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

        $this->checkBadClasses([
            'Espo\\Core\\Services\\Base',
            'Espo\\Core\\ORM\\Repositories\\RDB',
            'Espo\\Core\\Hooks\\Base',
            'Espo\\Core\\Acl\\Base',
            'Espo\\Core\\Acl\\Acl',
            'Espo\\Core\\AclPortal\\Base',
            'Espo\\Core\\AclPortal\\Acl',
            'Espo\\Core\\Injectable',
        ]);
    }

    /**
     * @param string[] $badList
     * @throws Exception
     * @noinspection PhpSameParameterValueInspection
     */
    private function checkBadClasses(array $badList): void
    {
        $skipPathRegexList = [
            '^custom\/Espo\/Modules\/[^\/]+\/vendor',
            '^custom\/Espo\/Custom\/vendor',
            '^application\/Espo\/Modules\/[^\/]+\/vendor',
            "^custom\/Espo\/Modules\/Sales\/",
        ];

        foreach (['custom', 'application/Espo/Modules'] as $dir) {
            $this->loadFromDir($dir, $skipPathRegexList);
        }

        $ignoreList = [
            'Espo\\Core\\Acl\\Acl',
            'Espo\\Core\\AclPortal\\Acl',
            'Espo\\Core\\Cleanup\\Base',
        ];

        $msg = '';

        foreach (get_declared_classes() as $class) {
            if (in_array($class, $ignoreList)) {
                continue;
            }

            foreach ($badList as $badClass) {
                if (is_subclass_of($class, $badClass) && $class !== $badClass) {
                    $msg .= "  $class is sub-class of $badClass\n";
                }
            }
        }

        if ($msg !== '') {
            $msg = "These classes should be fixed before upgrade. " .
                "They extend classes that are removed in the next version.\n\n" . $msg;

            throw new Exception($msg);
        }
    }

    private function loadFromDir(string $dir, array $skipPathRegexList): void
    {
        $directory = new RecursiveDirectoryIterator($dir);
        $fullTree = new RecursiveIteratorIterator($directory);
        $phpFiles = new RegexIterator($fullTree, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        foreach ($phpFiles as $path) {
            $file = $path[0];

            if ($this->toSkip($file, $skipPathRegexList)) {
                continue;
            }

            require_once($file);
        }
    }

    private function toSkip(string $file, array $skipPathRegexList): bool
    {
        foreach ($skipPathRegexList as $pattern) {
            if (preg_match('/' . $pattern . '/', $file)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Error
     */
    private function processCheckExtensions(): void
    {
        $errorMessageList = [];

        $this->processCheckExtension('Advanced Pack', '3.2.0', $errorMessageList);
        $this->processCheckExtension('VoIP Integration', '2.0.0', $errorMessageList);
        $this->processCheckExtension('Real Estate', '1.8.2', $errorMessageList);
        $this->processCheckExtension('Google Integration', '1.7.6', $errorMessageList);
        $this->processCheckExtension('Outlook Integration', '1.3.6', $errorMessageList);

        if (!count($errorMessageList)) {
            return;
        }

        $message = implode("\n\n", $errorMessageList);

        throw new Error($message);
    }

    private function processCheckExtension(string $name, string $minVersion, array &$errorMessageList): void
    {
        $em = $this->container->getByClass(EntityManager::class);

        $extension = $em->getRDBRepository(Extension::ENTITY_TYPE)
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
            "BugZyro 9.0 is not compatible with '$name' extension of versions lower than $minVersion. " .
            "Please upgrade the extension or uninstall it.";

        $errorMessageList[] = $message;
    }
}
