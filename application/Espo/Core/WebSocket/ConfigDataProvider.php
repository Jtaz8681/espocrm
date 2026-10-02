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

namespace Espo\Core\WebSocket;

use Espo\Core\Utils\Config;

/**
 * @since 9.1.0
 */
class ConfigDataProvider
{
    public function __construct(
        private Config $config,
    ) {}

    public function isEnabled(): bool
    {
        return (bool) $this->config->get('useWebSocket');
    }

    public function isDebugMode(): bool
    {
        return (bool) $this->config->get('webSocketDebugMode');
    }

    public function useSecureServer(): bool
    {
        return (bool) $this->config->get('webSocketUseSecureServer');
    }

    public function getPort(): ?string
    {
        $port = $this->config->get('webSocketPort');

        if (!$port) {
            return null;
        }

        return (string) $port;
    }

    public function getPhpExecutablePath(): ?string
    {
        return $this->config->get('phpExecutablePath');
    }

    public function getSslCertificateFile(): ?string
    {
        return $this->config->get('webSocketSslCertificateFile');
    }

    public function allowSelfSignedSsl(): bool
    {
        return (bool) $this->config->get('webSocketSslAllowSelfSigned');
    }

    public function getSslCertificatePassphrase(): ?string
    {
        return $this->config->get('webSocketSslCertificatePassphrase');
    }

    public function getSslCertificateLocalPrivateKey(): ?string
    {
        return $this->config->get('webSocketSslCertificateLocalPrivateKey');
    }

    public function getMessager(): ?string
    {
        return $this->config->get('webSocketMessager');
    }
}
