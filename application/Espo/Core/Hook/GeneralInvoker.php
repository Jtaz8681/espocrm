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

namespace Espo\Core\Hook;

use Espo\Core\Hook\Hook\AfterMassRelate;
use Espo\Core\Hook\Hook\AfterRelate;
use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\Hook\Hook\AfterUnrelate;
use Espo\Core\Hook\Hook\BeforeRemove;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Hook\Hook\LateAfterRemove;
use Espo\Core\Hook\Hook\LateAfterSave;
use Espo\ORM\Entity;
use Espo\ORM\Query\Select;
use Espo\ORM\Repository\Option\MassRelateOptions;
use Espo\ORM\Repository\Option\RelateOptions;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\ORM\Repository\Option\UnrelateOptions;
use LogicException;

/**
 * Invokes hook methods, prepares needed arguments for specific hooks.
 */
class GeneralInvoker
{
    private const HOOK_BEFORE_SAVE = 'beforeSave';
    private const HOOK_AFTER_SAVE = 'afterSave';
    private const HOOK_LATE_AFTER_SAVE = 'lateAfterSave';
    private const HOOK_BEFORE_REMOVE = 'beforeRemove';
    private const HOOK_AFTER_REMOVE = 'afterRemove';
    private const HOOK_LATE_AFTER_REMOVE = 'lateAfterRemove';
    private const HOOK_AFTER_RELATE = 'afterRelate';
    private const HOOK_AFTER_UNRELATE = 'afterUnrelate';
    private const HOOK_AFTER_MASS_RELATE = 'afterMassRelate';

    /**
     * @param object $hook A hook object.
     * @param string $name A hook name.
     * @param mixed $subject A subject.
     * @param array<string, mixed> $options Options.
     * @param array<string, mixed> $hookData Additional data.
     */
    public function invoke(
        object $hook,
        string $name,
        mixed $subject,
        array $options,
        array $hookData
    ): void {

        if ($name === self::HOOK_BEFORE_SAVE && $hook instanceof BeforeSave) {
            if (!$subject instanceof Entity) {
                throw new LogicException();
            }

            $hook->beforeSave($subject, SaveOptions::fromAssoc($options));

            return;
        }

        if ($name === self::HOOK_AFTER_SAVE && $hook instanceof AfterSave) {
            if (!$subject instanceof Entity) {
                throw new LogicException();
            }

            $hook->afterSave($subject, SaveOptions::fromAssoc($options));

            return;
        }

        if ($name === self::HOOK_LATE_AFTER_SAVE && $hook instanceof LateAfterSave) {
            if (!$subject instanceof Entity) {
                throw new LogicException();
            }

            $hook->lateAfterSave($subject, SaveOptions::fromAssoc($options));

            return;
        }

        if ($name === self::HOOK_BEFORE_REMOVE &&  $hook instanceof BeforeRemove) {
            if (!$subject instanceof Entity) {
                throw new LogicException();
            }

            $hook->beforeRemove($subject, RemoveOptions::fromAssoc($options));

            return;
        }

        if ($name === self::HOOK_AFTER_REMOVE && $hook instanceof AfterRemove) {
            if (!$subject instanceof Entity) {
                throw new LogicException();
            }

            $hook->afterRemove($subject, RemoveOptions::fromAssoc($options));

            return;
        }

        if ($name === self::HOOK_LATE_AFTER_REMOVE && $hook instanceof LateAfterRemove) {
            if (!$subject instanceof Entity) {
                throw new LogicException();
            }

            $hook->lateAfterRemove($subject, RemoveOptions::fromAssoc($options));

            return;
        }

        if ($name === self::HOOK_AFTER_RELATE && $hook instanceof AfterRelate) {
            $relationName = $hookData['relationName'] ?? null;
            $relatedEntity = $hookData['foreignEntity'] ?? null;
            $columnData = $hookData['relationData'] ?? [];

            if (
                !$subject instanceof Entity ||
                !is_string($relationName) ||
                !$relatedEntity instanceof Entity
            ) {
                throw new LogicException();
            }

            $hook->afterRelate(
                $subject,
                $relationName,
                $relatedEntity,
                $columnData,
                RelateOptions::fromAssoc($options)
            );

            return;
        }

        if ($name === self::HOOK_AFTER_UNRELATE && $hook instanceof AfterUnrelate) {
            $relationName = $hookData['relationName'] ?? null;
            $relatedEntity = $hookData['foreignEntity'] ?? null;

            if (
                !$subject instanceof Entity ||
                !is_string($relationName) ||
                !$relatedEntity instanceof Entity
            ) {
                throw new LogicException();
            }

            $hook->afterUnrelate(
                $subject,
                $relationName,
                $relatedEntity,
                UnrelateOptions::fromAssoc($options)
            );

            return;
        }

        if ($name === self::HOOK_AFTER_MASS_RELATE && $hook instanceof AfterMassRelate) {
            $relationName = $hookData['relationName'] ?? null;
            $query = $hookData['query'] ?? null;
            $columnData = $hookData['relationData'] ?? []; // Not implemented currently.

            if (
                !$subject instanceof Entity ||
                !is_string($relationName) ||
                !$query instanceof Select
            ) {
                throw new LogicException();
            }

            $hook->afterMassRelate(
                $subject,
                $relationName,
                $query,
                $columnData,
                MassRelateOptions::fromAssoc($options)
            );

            return;
        }

        $hook->$name($subject, $options, $hookData);
    }
}
