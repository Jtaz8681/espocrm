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

namespace Espo\Core\Exceptions\Error;

use Espo\Core\Utils\Json;

/**
 * A wrapper for error message data for the frontend. Supposed to be passed encoded to `createWithBody`
 * methods of exceptions.
 */
class Body
{
    private ?string $messageTranslationLabel = null;
    private ?string $messageTranslationScope = null;
    /** @var ?array<string, string> */
    private ?array $messageTranslationData = null;
    private ?string $message = null;

    public static function create(): self
    {
        return new self();
    }

    /**
     * A translatable message to display in frontend. Labels should be in the `messages` category.
     *
     * @param ?array<string, string> $data
     */
    public function withMessageTranslation(string $label, ?string $scope = null, ?array $data = null): self
    {
        $obj = clone $this;

        $obj->messageTranslationLabel = $label;
        $obj->messageTranslationScope = $scope;
        $obj->messageTranslationData = $data;

        return $obj;
    }

    public function withMessage(string $message): self
    {
        $obj = clone $this;
        $obj->message = $message;

        return $obj;
    }

    public function encode(): string
    {
        $data = (object) [];

        if ($this->messageTranslationLabel) {
            $messageTranslationData = (object) ($this->messageTranslationData ?? []);

            $data->messageTranslation = (object) [
                'label' => $this->messageTranslationLabel,
                'scope' => $this->messageTranslationScope,
                'data' => $messageTranslationData,
            ];
        }

        if ($this->message) {
            $data->message = $this->message;
        }

        return Json::encode($data);
    }
}
