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

namespace Espo\Core\Utils\Client\ActionRenderer;

use Espo\Core\Utils\Client\Script;

/**
 * Immutable.
 */
class Params
{
    /** @var ?array<string, mixed> */
    private ?array $data;
    private bool $initAuth = false;
    /** @var string[] */
    private array $frameAncestors = [];
    /** @var Script[] */
    private array $scripts = [];
    private ?string $pageTitle = null;
    private ?string $theme = null;

    /**
     * @param ?array<string, mixed> $data
     */
    public function __construct(
        private string $controller,
        private string $action,
        ?array $data = null
    ) {
        $this->data = $data;
    }

    /**
     * @param ?array<string, mixed> $data
     */
    public static function create(string $controller, string $action, ?array $data = null): self
    {
        return new self($controller, $action, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function withData(array $data): self
    {
        $obj = clone $this;
        $obj->data = $data;

        return $obj;
    }

    public function withInitAuth(bool $initAuth = true): self
    {
        $obj = clone $this;
        $obj->initAuth = $initAuth;

        return $obj;
    }

    /**
     * @param string[] $frameAncestors
     * @since 9.0.0
     */
    public function withFrameAncestors(array $frameAncestors): self
    {
        $obj = clone $this;
        $obj->frameAncestors = $frameAncestors;

        return $obj;
    }

    /**
     * @param Script[] $scripts
     * @since 9.0.0
     */
    public function withScripts(array $scripts): self
    {
        $obj = clone $this;
        $obj->scripts = $scripts;

        return $obj;
    }

    /**
     * @since 9.1.0
     */
    public function withPageTitle(?string $pageTitle): self
    {
        $obj = clone $this;
        $obj->pageTitle = $pageTitle;

        return $obj;
    }

    /**
     * @since 9.1.0
     */
    public function withTheme(?string $theme): self
    {
        $obj = clone $this;
        $obj->theme = $theme;

        return $obj;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return ?array<string, mixed>
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    public function initAuth(): bool
    {
        return $this->initAuth;
    }

    /**
     * @return string[]
     * @since 9.0.0
     */
    public function getFrameAncestors(): array
    {
        return $this->frameAncestors;
    }

    /**
     * @return Script[]
     * @since 9.0.0
     */
    public function getScripts(): array
    {
        return $this->scripts;
    }

    /**
     * @since 9.1.0
     */
    public function getPageTitle(): ?string
    {
        return $this->pageTitle;
    }

    /**
     * @since 9.1.0
     */
    public function getTheme(): ?string
    {
        return $this->theme;
    }
}
