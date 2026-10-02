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

namespace Espo\Core\FieldValidation\Exceptions;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error\Body;
use Espo\Core\Exceptions\HasLogMessage;
use Espo\Core\FieldValidation\Failure;

use LogicException;

final class ValidationError extends BadRequest implements HasLogMessage
{
    private ?Failure $failure = null;

    /**
     * Create with a body (supposed to be sent to the frontend).
     * Body object is supported since v8.1.
     */
    public static function createWithBody(string $reason, string|Body $body): self
    {
        if ($body instanceof Body) {
            $body = $body->encode();
        }

        $exception = parent::createWithBody($reason, $body);

        if (!$exception instanceof self) {
            throw new LogicException();
        }

        return $exception;
    }

    public static function create(Failure $failure): self
    {
        $exception = self::createWithBody(
            'validationFailure',
            Body::create()
                ->withMessageTranslation('validationFailure', null, [
                    'field' => $failure->getField(),
                    'type' => $failure->getType(),
                ])
                ->encode()
        );

        $exception->failure = $failure;

        return $exception;
    }

    /**
     * @since 10.0.0
     */
    public function hasFailure(): bool
    {
        return $this->failure !== null;
    }

    public function getFailure(): Failure
    {
        if (!$this->failure) {
            throw new LogicException();
        }

        return $this->failure;
    }

    public function getLogMessage(): string
    {
        if (!$this->failure) {
            return "Field validation failure.";
        }

        $entityType = $this->failure->getEntityType();
        $field = $this->failure->getField();
        $type = $this->failure->getType();

        return "Field validation failure; " .
            "entityType: {$entityType}, field: {$field}, type: {$type}.";
    }
}
