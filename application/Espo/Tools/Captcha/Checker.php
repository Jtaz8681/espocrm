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

namespace Espo\Tools\Captcha;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Json;
use Espo\Core\Utils\Log;
use Espo\Entities\Integration;
use Espo\ORM\EntityManager;
use RuntimeException;

class Checker
{
    private const URL = 'https://www.google.com/recaptcha/api/siteverify';
    private const SCORE_THRESHOLD = 0.2;
    private const TIMEOUT = 20;

    public function __construct(
        private EntityManager $entityManager,
        private Log $log,
    ) {}

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function check(string $token, string $action): void
    {
        [$secret, $scoreThreshold] = $this->getCaptchaSecretKey();

        if ($secret && $token === '') {
            throw new BadRequest("No captcha token.");
        }

        if (!$secret) {
            throw new Forbidden("Captcha not configured.");
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::URL);
        curl_setopt($ch, CURLOPT_POST, true);

        $data = [
            'secret' => $secret,
            'response' => $token,
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);

        $response = curl_exec($ch);

        if (!is_string($response)) {
            $message = "CURL failure.";

            $curlError = curl_error($ch);

            if ($curlError) {
                $message .= " " . $curlError;
            }

            throw new RuntimeException($message);
        }

        $responseData = Json::decode($response, true);

        if (!is_array($responseData)) {
            throw new RuntimeException("Bad response from ReCaptcha.");
        }

        $success = $responseData['success'] ?? null;
        $score = $responseData['score'] ?? null;
        $resultAction = $responseData['action'] ?? null;

        if (!$success) {
            $this->log->error("Captcha error; action: {action}; response: {response}", [
                'action' => $action,
                'response' => $response,
            ]);

            throw new Forbidden("ReCaptcha error.");
        }

        if (!is_string($resultAction)) {
            throw new RuntimeException("No or bad action in ReCaptcha response.");
        }

        if (!is_int($score) && !is_float($score)) {
            throw new RuntimeException("No score in ReCaptcha response.");
        }

        if ($action !== $resultAction) {
            throw new Forbidden("ReCaptcha action mismatch.");
        }

        if ($score < $scoreThreshold) {
            throw new Forbidden("ReCaptcha low score.");
        }
    }

    /**
     * @return array{?string, ?int}
     */
    private function getCaptchaSecretKey(): array
    {
        $entity = $this->entityManager
            ->getRepositoryByClass(Integration::class)
            ->getById('GoogleReCaptcha');

        if (!$entity) {
            return [null, null];
        }

        $secretKey = $entity->get('secretKey');
        $scoreThreshold = $entity->get('scoreThreshold') ?? self::SCORE_THRESHOLD;

        if (!$secretKey) {
            return [null, null];
        }

        return [$secretKey, $scoreThreshold];
    }
}
