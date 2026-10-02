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

namespace Espo\Core\Webhook;

use Espo\Core\Exceptions\Error;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Json;
use Espo\Core\Utils\Security\UrlCheck;
use Espo\Entities\Webhook;
use Espo\Core\HttpClient;
use LogicException;
use Psr\Http\Message\RequestInterface;
use SensitiveParameter;

/**
 * Sends a portion.
 */
class Sender
{
    private const int CONNECT_TIMEOUT = 5;
    private const int TIMEOUT = 10;

    public function __construct(
        private Config $config,
        private UrlCheck $urlCheck,
        private HttpClient\ClientFactory $clientFactory,
    ) {}

    /**
     * @param array<int, mixed> $dataList
     * @throws Error
     */
    public function send(Webhook $webhook, array $dataList): int
    {
        $payload = Json::encode($dataList);

        [$signature, $legacySignature] = $this->prepareSignatures($webhook, $payload);

        $options = new HttpClient\Options(
            protocols: [HttpClient\Protocol::https, HttpClient\Protocol::http],
            redirect: new HttpClient\Options\Redirect(
                allow: true,
                protocols: [HttpClient\Protocol::https],
            ),
            timeout: $this->getTimeout(),
            connectTimeout: $this->getConnectTimeout(),
            internalHostRestriction: new HttpClient\Options\InternalHostRestriction(
                restrict: true,
                allowed: $this->getAllowedAddressList(),
            ),
        );

        $request = $this->prepareRequest(
            url: $this->getUrl($webhook),
            payload: $payload,
            signature: $signature,
            legacySignature: $legacySignature,
        );

        $client = $this->clientFactory->create($options);

        try {
            $response = $client->send($request);
        } catch (HttpClient\Exceptions\ConnectException $e) {
            if ($e->getReason() === HttpClient\ConnectErrorReason::Timeout) {
                return 408;
            }

            throw new Error("Connect error.", previous: $e);
        } catch (HttpClient\Exceptions\TooManyRedirectsException $e) {
            throw new Error("Too many redirects.", previous: $e);
        }

        return $response->getStatusCode();
    }

    private function buildSignature(Webhook $webhook, string $payload, #[SensitiveParameter] string $secretKey): string
    {
        $webhookId = $webhook->getId();
        $hash = hash_hmac('sha256', $payload, $secretKey);

        return base64_encode("$webhookId:$hash");
    }

    /**
     * @todo Remove in v11.0.
     */
    private function buildSignatureLegacy(Webhook $webhook, string $payload, string $secretKey): string
    {
        return base64_encode($webhook->getId() . ':' . hash_hmac('sha256', $payload, $secretKey, true));
    }

    /**
     * @return string
     * @throws Error
     */
    private function getUrl(Webhook $webhook): string
    {
        $url = $webhook->getUrl() ?? throw new Error("Webhook does not have URL.");

        if (!$this->urlCheck->isUrl($url)) {
            throw new Error("'$url' is not valid URL.");
        }

        return $url;
    }

    /**
     * @return string[]
     */
    private function getAllowedAddressList(): array
    {
        /** @var string[] $allowedAddressList */
        $allowedAddressList = $this->config->get('webhookAllowedAddressList') ?? [];

        return $allowedAddressList;
    }

    private function prepareRequest(
        string $url,
        string $payload,
        ?string $signature,
        ?string $legacySignature,
    ): RequestInterface {

        $request = HttpClient\RequestCreator::create('POST', $url)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', (string) strlen($payload));

        if ($signature) {
            $request = $request->withHeader('Signature', $signature);
        }

        if ($legacySignature) {
            $request = $request->withHeader('X-Signature', $legacySignature);
        }

        $request = $request->withBody(HttpClient\Util::streamFor($payload));

        if (!$request instanceof RequestInterface) {
            throw new LogicException();
        }

        return $request;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function prepareSignatures(Webhook $webhook, string $payload): array
    {
        $signature = null;
        $legacySignature = null;

        $secretKey = $webhook->getSecretKey();

        if ($secretKey) {
            $signature = $this->buildSignature($webhook, $payload, $secretKey);
            $legacySignature = $this->buildSignatureLegacy($webhook, $payload, $secretKey);
        }

        return [$signature, $legacySignature];
    }

    private function getConnectTimeout(): ?int
    {
        return $this->config->get('webhookConnectTimeout', self::CONNECT_TIMEOUT);
    }

    private function getTimeout(): ?int
    {
        return $this->config->get('webhookTimeout', self::TIMEOUT);
    }
}
