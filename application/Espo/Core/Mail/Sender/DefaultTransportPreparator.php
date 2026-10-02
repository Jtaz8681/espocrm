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

namespace Espo\Core\Mail\Sender;

use Espo\Core\Mail\SmtpParams;
use Espo\Core\Utils\Config;
use RuntimeException;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\Auth\CramMd5Authenticator;
use Symfony\Component\Mailer\Transport\Smtp\Auth\LoginAuthenticator;
use Symfony\Component\Mailer\Transport\Smtp\Auth\PlainAuthenticator;
use Symfony\Component\Mailer\Transport\Smtp\Auth\XOAuth2Authenticator;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;

class DefaultTransportPreparator implements TransportPreparator
{
    public function __construct(
        private Config $config,
    ) {}

    public function prepare(SmtpParams $smtpParams): TransportInterface
    {
        $localHostName = $this->config->get('smtpLocalHostName', gethostname());

        // 'SSL' is treated as implicit SSL/TLS. 'TLS' is treated as STARTTLS.
        // STARTTLS is the most common method.
        $scheme = $smtpParams->getSecurity() === SmtpParams::SECURITY_SSL_TLS ? 'smtps' : 'smtp';

        if ($smtpParams->getSecurity() === SmtpParams::SECURITY_START_TLS && !defined('OPENSSL_VERSION_NUMBER')) {
            throw new RuntimeException("OpenSSL is not available.");
        }

        $transport = (new EsmtpTransportFactory())
            ->create(
                new Dsn(
                    scheme: $scheme,
                    host: $smtpParams->getServer(),
                    port: $smtpParams->getPort(),
                )
            );

        if (!$smtpParams->getSecurity() && $transport instanceof EsmtpTransport) {
            $transport->setAutoTls(false);
        }

        if (!$transport instanceof EsmtpTransport) {
            throw new RuntimeException();
        }

        $transport->setLocalDomain($localHostName);

        $authMechanism = null;

        // @todo For xoauth, set authMechanism, username, password in handlers.
        $connectionOptions = $smtpParams->getConnectionOptions() ?? [];
        $authString = $connectionOptions['authString'] ?? null;

        if ($authString) {
            $decodedAuthString = base64_decode($authString);

            /** @noinspection RegExpRedundantEscape */
            if (preg_match("/user=(.*?)\\\1auth=Bearer (.*?)\\\1\\\1/", $decodedAuthString, $matches) !== false) {
                $username = $matches[1];
                $token = $matches[2];

                $transport->setUsername($username);
                $transport->setPassword($token);
            }

            $authMechanism = SmtpParams::AUTH_MECHANISM_XOAUTH;
        } else if ($smtpParams->useAuth()) {
            $authMechanism = $smtpParams->getAuthMechanism() ?: SmtpParams::AUTH_MECHANISM_LOGIN;

            $transport->setUsername($smtpParams->getUsername() ?? '');
            $transport->setPassword($smtpParams->getPassword() ?? '');
        }

        if ($authMechanism === SmtpParams::AUTH_MECHANISM_LOGIN) {
            $transport->setAuthenticators([new LoginAuthenticator()]);
        } else if ($authMechanism === SmtpParams::AUTH_MECHANISM_CRAMMD5) {
            $transport->setAuthenticators([new CramMd5Authenticator()]);
        } else if ($authMechanism === SmtpParams::AUTH_MECHANISM_PLAIN) {
            $transport->setAuthenticators([new PlainAuthenticator()]);
        } else if ($authMechanism === SmtpParams::AUTH_MECHANISM_XOAUTH) {
            $transport->setAuthenticators([new XOAuth2Authenticator()]);
        }

        return $transport;
    }
}
