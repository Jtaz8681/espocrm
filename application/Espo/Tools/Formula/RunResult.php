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

namespace Espo\Tools\Formula;

use Espo\Core\Formula\Exceptions\SyntaxError;
use Espo\Core\Formula\Exceptions\Error;

use stdClass;

class RunResult
{
    private bool $isSuccess = false;
    private ?string $output = null;
    private ?string $message = null;
    private ?Error $exception = null;

    private function __construct(bool $isSuccess)
    {
        $this->isSuccess = $isSuccess;
    }

    public static function createSuccess(?string $output): self
    {
        $obj = new self(true);
        $obj->output = $output;

        return $obj;
    }

    public static function createError(Error $exception, ?string $output): self
    {
        $obj = new self(false);

        $obj->message = $exception->getMessage();
        $obj->exception = $exception;
        $obj->output = $output;

        return $obj;
    }

    public static function createSyntaxError(SyntaxError $exception): self
    {
        $obj = new self(false);

        $obj->message = $exception->getShortMessage();
        $obj->exception = $exception;

        return $obj;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getException(): ?Error
    {
        return $this->exception;
    }

    public function toStdClass(): stdClass
    {
        $data = (object) [];

        $data->isSuccess = $this->isSuccess();

        if (!$this->isSuccess) {
            $data->message = $this->message;
            $data->isSyntaxError = $this->exception instanceof SyntaxError;
        }

        $data->output = $this->output;

        return $data;
    }
}
