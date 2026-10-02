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

namespace Espo\Entities;

use Espo\Core\ORM\Entity;
use Espo\ORM\Defs\Params\AttributeParam;

class AppLogRecord extends Entity
{
    public const ENTITY_TYPE = 'AppLogRecord';

    public function setMessage(string $message): self
    {
        $this->set('message', $message);

        return $this;
    }

    public function setLevel(string $level): self
    {
        $this->set('level', $level);

        return $this;
    }

    public function setCode(?int $code): self
    {
        $this->set('code', $code);

        return $this;
    }

    public function setExceptionClass(?string $exceptionClass): self
    {
        $len = $this->getAttributeParam('exceptionClass', AttributeParam::LEN);

        if ($exceptionClass && strlen($exceptionClass) > $len) {
            $exceptionClass = substr($exceptionClass, $len);
        }

        $this->set('exceptionClass', $exceptionClass);

        return $this;
    }

    public function setFile(?string $file): self
    {
        $len = $this->getAttributeParam('file', AttributeParam::LEN);

        if ($file && strlen($file) > $len) {
            $file = substr($file, $len);
        }

        $this->set('file', $file);

        return $this;
    }

    public function setLine(?int $code): self
    {
        $this->set('line', $code);

        return $this;
    }

    public function setRequestMethod(?string $requestMethod): self
    {
        $this->set('requestMethod', $requestMethod);

        return $this;
    }

    public function setRequestResourcePath(?string $requestResourcePath): self
    {
        $len = $this->getAttributeParam('requestResourcePath', AttributeParam::LEN);

        if ($requestResourcePath && strlen($requestResourcePath) > $len) {
            $requestResourcePath = substr($requestResourcePath, $len);
        }

        $this->set('requestResourcePath', $requestResourcePath);

        return $this;
    }
}
