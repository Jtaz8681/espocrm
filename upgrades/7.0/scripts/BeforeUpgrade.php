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

use Espo\Core\Exceptions\Error;

use Espo\Core\Container;

class BeforeUpgrade
{
    public function run(Container $container)
    {
        $this->container = $container;

        $this->processCheckExtensions();
        $this->processCheckCache();

        // Load to prevent fail if run in a single process.
        $container->get('entityManager')
            ->getQueryBuilder()
            ->update()
            ->in('Test')
            ->set(['test' => 'test'])
            ->build();
    }

    private function processCheckCache()
    {
        $isCli = (substr(php_sapi_name(), 0, 3) == 'cli') ? true : false;

        if (!$isCli) {
            return;
        }

        $cacheParam = 'opcache.enable_cli';

        $value = ini_get($cacheParam);

        if ($value === '1') {
            throw new Error("PHP parameter '{$cacheParam}' should be set to '0'.");
        }
    }

    private function processCheckExtensions(): void
    {
        $errorMessageList = [];

        $this->processCheckExtension('Advanced Pack', '2.8.0', $errorMessageList);
        $this->processCheckExtension('Sales Pack', '1.1.4', $errorMessageList);
        $this->processCheckExtension('Outlook Integration', '1.2.5', $errorMessageList);
        $this->processCheckExtension('MailChimp Integration', '1.0.8', $errorMessageList);
        $this->processCheckExtension('Real Estate', '1.5.0', $errorMessageList);
        $this->processCheckExtension('VoIP Integration', '1.17.3', $errorMessageList);

        if (!count($errorMessageList)) {
            return;
        }

        $message = implode("\n\n", $errorMessageList);

        throw new Error($message);
    }

    private function processCheckExtension(string $name, string $minVersion, array &$errorMessageList): void
    {
        $em = $this->container->get('entityManager');

        $extension = $em->getRepository('Extension')
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
            "EspoCRM 7.0 is not compatible with '{$name}' extension of a version lower than {$minVersion}. " .
            "Please upgrade the extension or uninstall it. Then run the upgrade command again.";

        $errorMessageList[] = $message;
    }
}
