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

namespace Espo\ORM\Repository\Option;

use Closure;
use Espo\Core\Utils\Util;
use LogicException;

/**
 * A save context.
 *
 * If a save invokes another save, the context instance should not be re-used.
 * If a save invokes a relate action, the context can be passed to that action.
 *
 * @since 10.0.0
 *
 * Before v10.0.0, since 9.1.0 it was located in the `Core` namespace.
 */
class SaveContext
{
    public const NAME = 'context';

    private string $actionId;
    private bool $linkUpdated = false;
    private ?bool $isNew = null;

    /** @var Closure[] */
    private array $deferredActions = [];

    /**
     * @param ?string $actionId An action ID.
     */
    public function __construct(
        ?string $actionId = null,
    ) {
        $this->actionId = $actionId ?? Util::generateId();
    }

    /**
     * An action ID. Used to group notifications. If a save invokes another save, the same ID can be re-used,
     * but the context instance should not be re-used. Create a derived context for this.
     *
     * @since 9.2.0
     */
    public function getActionId(): string
    {
        return $this->actionId;
    }

    /**
     * @deprecated Since v9.2.0. Use `getActionId`.
     */
    public function getId(): string
    {
        return $this->getActionId();
    }

    public function setLinkUpdated(): self
    {
        $this->linkUpdated = true;

        return $this;
    }

    public function isLinkUpdated(): bool
    {
        return $this->linkUpdated;
    }

    /**
     * Obtain from save options.
     *
     * @return ?self
     * @since 9.2.0.
     */
    public static function obtainFromOptions(SaveOptions $options): ?self
    {
        $saveContext = $options->get(self::NAME);

        if (!$saveContext instanceof self) {
            return null;
        }

        return $saveContext;
    }

    /**
     * Obtain from raw save options.
     *
     * @param array<string, mixed> $options
     * @return ?self
     * @since 9.2.0.
     */
    public static function obtainFromRawOptions(array $options): ?self
    {
        $saveContext = $options[self::NAME] ?? null;

        if (!$saveContext instanceof self) {
            return null;
        }

        return $saveContext;
    }

    /**
     * Add a deferred action.
     *
     * @param Closure $callback A callback.
     * @since 9.2.0.
     */
    public function addDeferredAction(Closure $callback): void
    {
        $this->deferredActions[] = $callback;
    }

    /**
     * @internal
     * @since 9.2.0.
     */
    public function callDeferredActions(): void
    {
        foreach ($this->deferredActions as $callback) {
            $callback();
        }

        $this->deferredActions = [];
    }

    /**
     * Create a derived context. To be used for nested saves.
     *
     * @since 9.2.0
     */
    public function createDerived(): self
    {
        return new self($this->actionId);
    }

    /**
     * @internal
     * @since 10.0.0
     */
    public function setIsNew(bool $isNew): void
    {
        if ($this->isNew !== null) {
            throw new LogicException("Cannot set already set isNew.");
        }

        $this->isNew = $isNew;
    }

    /**
     * Was the entity new before save. Can be accessed only after save is started.
     * To be used for late hooks, when then entity is already not new, to check.
     *
     * @since 10.0.0
     */
    public function isNew(): bool
    {
        return $this->isNew ?? throw new LogicException("Cannot access isNew before it's set.");
    }
}
