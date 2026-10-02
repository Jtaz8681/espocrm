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

use Espo\Core\Utils\Metadata;

use React\EventLoop\Factory as EventLoopFactory;
use React\Socket\Server as SocketServer;
use React\Socket\SecureServer as SocketSecureServer;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

/**
 * Starts a web-socket server.
 */
class ServerStarter
{
    /** @var array<string, array<string, mixed>> */
    private array $categoriesData;
    private ?string $phpExecutablePath;
    private bool $isDebugMode;
    private bool $useSecureServer;
    private string $port;

    public function __construct(
        private Subscriber $subscriber,
        private ConfigDataProvider $configDataProvider,
        Metadata $metadata
    ) {
        $this->categoriesData = $metadata->get(['app', 'webSocket', 'categories'], []);

        $this->phpExecutablePath = $this->configDataProvider->getPhpExecutablePath();
        $this->isDebugMode = $this->configDataProvider->isDebugMode();
        $this->useSecureServer = $this->configDataProvider->useSecureServer();
        $port = $this->configDataProvider->getPort();

        if (!$port) {
            $port = $this->useSecureServer ? '8443' : '8080';
        }

        $this->port = $port;
    }

    /**
     * Start a web-socket server.
     */
    public function start(): void
    {
        $loop = EventLoopFactory::create();

        $pusher = new Pusher($this->categoriesData, $this->phpExecutablePath, $this->isDebugMode);

        $this->subscriber->subscribe($pusher, $loop);

        $socketServer = new SocketServer('0.0.0.0:' . $this->port, $loop);

        if ($this->useSecureServer) {
            $sslParams = $this->getSslParams();

            $socketServer = new SocketSecureServer($socketServer, $loop, $sslParams);
        }

        $wsServer = new WsServer(new Ratchet\EspoWampServer($pusher));
        $wsServer->enableKeepAlive($loop, 60);

        new IoServer(
            new HttpServer($wsServer),
            $socketServer
        );

        $loop->run();
    }

    /**
     * @return array<string, mixed>
     */
    private function getSslParams(): array
    {
        $sslParams = [
            'local_cert' => $this->configDataProvider->getSslCertificateFile(),
            'allow_self_signed' => $this->configDataProvider->allowSelfSignedSsl(),
            'verify_peer' => false,
        ];

        if ($this->configDataProvider->getSslCertificatePassphrase()) {
            $sslParams['passphrase'] = $this->configDataProvider->getSslCertificatePassphrase();
        }

        if ($this->configDataProvider->getSslCertificateLocalPrivateKey()) {
            $sslParams['local_pk'] = $this->configDataProvider->getSslCertificateLocalPrivateKey();
        }

        return $sslParams;
    }
}
