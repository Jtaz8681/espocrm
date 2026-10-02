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

/** @noinspection PhpMultipleClassDeclarationsInspection */
class BeforeUpgrade
{
    private ?Container $container = null;

    /**
     * @throws Error
     */
    public function run(Container $container)
    {
        $this->container = $container;

        $this->processCheckExtensions();
    }

    /**
     * @throws Error
     */
    private function processCheckExtensions(): void
    {
        $errorMessageList = [];

        $this->processCheckExtension('Advanced Pack', '3.0.0', $errorMessageList);
        $this->processCheckExtension('Google Integration', '1.6.0', $errorMessageList);

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
            "BugZyro 8.0 is not compatible with '$name' extension of versions lower than $minVersion. " .
            "Please upgrade the extension or uninstall it.";

        $errorMessageList[] = $message;
    }
}
