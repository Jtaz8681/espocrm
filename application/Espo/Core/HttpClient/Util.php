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

namespace Espo\Core\HttpClient;

use Espo\Core\HttpClient\Exceptions\ConnectException;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp;
use Psr\Http\Message\StreamInterface;

class Util
{
    /**
     * @param resource|string|int|float|bool|StreamInterface $resource
     * @since 10.0.0
     */
    public static function streamFor($resource): StreamInterface
    {
        return Utils::streamFor($resource);
    }

    /**
     * @internal
     * @param string[] $addressList
     */
    public static function matchUrlToAddressList(string $url, array $addressList): bool
    {
        if (!$addressList) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (!is_string($host)) {
            return false;
        }

        if (!is_int($port)) {
            if ($scheme === 'https') {
                $port = 443;
            } else if ($scheme === 'http') {
                $port = 80;
            }
        }

        if (!is_int($port)) {
            return false;
        }

        $address = $host . ':' . $port;

        return in_array($address, $addressList);
    }

    /**
     * @internal
     * @throws ConnectException
     */
    public static function handleConnectException(GuzzleHttp\Exception\ConnectException $exception): never
    {
        $context = $exception->getHandlerContext();

        $reason = null;

        if (($context['errno'] ?? 0) === CURLE_OPERATION_TIMEDOUT) {
            $reason = ConnectErrorReason::Timeout;
        }

        throw ConnectException::create(previous: $exception, reason: $reason);
    }
}
