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

namespace Espo\Classes\RecordHooks\EmailAccount;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Security\HostCheck;
use Espo\Entities\EmailAccount;
use Espo\Entities\InboundEmail;
use Espo\ORM\Entity;

/**
 * @implements SaveHook<EmailAccount|InboundEmail>
 */
class BeforeSaveValidateHosts implements SaveHook
{
    public function __construct(
        private Config $config,
        private HostCheck $hostCheck,
    ) {}

    public function process(Entity $entity): void
    {
        if ($entity->isAttributeChanged('host') || $entity->isAttributeChanged('port')) {
            $this->validateImap($entity);
        }

        if ($entity->isAttributeChanged('smtpHost') || $entity->isAttributeChanged('smtpPort')) {
            $this->validateSmtp($entity);
        }
    }

    /**
     * @throws Forbidden
     */
    private function validateImap(EmailAccount|InboundEmail $entity): void
    {
        $host = $entity->getHost();
        $port = $entity->getPort();

        if ($host === null || $port === null) {
            return;
        }

        $address = $host . ':' . $port;

        if (in_array($address, $this->getAllowedAddressList())) {
            return;
        }

        if (!$this->hostCheck->isHostAndNotInternal($host)) {
            $message = $this->composeErrorMessage($host, $address);

            throw new Forbidden($message);
        }
    }

    /**
     * @throws Forbidden
     */
    private function validateSmtp(EmailAccount|InboundEmail $entity): void
    {
        $host = $entity->getSmtpHost();
        $port = $entity->getSmtpPort();

        if ($host === null || $port === null) {
            return;
        }

        $address = $host . ':' . $port;

        if (in_array($address, $this->getAllowedAddressList())) {
            return;
        }

        if (!$this->hostCheck->isHostAndNotInternal($host)) {
            $message = $this->composeErrorMessage($host, $address);

            throw new Forbidden($message);
        }
    }

    /**
     * @return string[]
     */
    private function getAllowedAddressList(): array
    {
        return $this->config->get('emailServerAllowedAddressList') ?? [];
    }

    private function composeErrorMessage(string $host, string $address): string
    {
        return "Host '$host' is not allowed as it's internal. " .
            "To allow, add `$address` to the config parameter `emailServerAllowedAddressList`.";
    }
}
