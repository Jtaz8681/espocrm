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

namespace Espo\Tools\Pdf;

use Espo\Core\InjectableFactory;
use RuntimeException;

class Builder
{
    private ?Template $template = null;
    private ?string $engine = null;

    public function __construct(private InjectableFactory $injectableFactory) {}

    public function setTemplate(Template $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function setEngine(string $engine): self
    {
        $this->engine = $engine;

        return $this;
    }

    public function build(): PrinterController
    {
        if (!$this->engine) {
            throw new RuntimeException('Engine is not set.');
        }

        if (!$this->template) {
            throw new RuntimeException('Template is not set.');
        }

        return $this->injectableFactory->createWith(
            PrinterController::class,
            [
                'template' => $this->template,
                'engine' => $this->engine,
            ]
        );
    }
}
