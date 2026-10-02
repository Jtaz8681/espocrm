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

namespace Espo\Hooks\Common;

use Espo\Core\Hook\Hook\AfterRelate;
use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\Hook\Hook\AfterUnrelate;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\ORM\Entity;
use Espo\ORM\Repository\Option\RelateOptions;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\ORM\Repository\Option\UnrelateOptions;
use Espo\Tools\Stream\HookProcessor;

class Stream implements BeforeSave, AfterSave, AfterRemove, AfterRelate, AfterUnrelate
{
    public static int $order = 9;

    public function __construct(private HookProcessor $processor)
    {}

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        if ($options->get(SaveOption::SILENT)) {
            return;
        }

        $this->processor->beforeSave($entity, $options);
    }

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
        if ($options->get(SaveOption::SILENT)) {
            return;
        }

        $this->processor->afterSave($entity, $options->toAssoc());
    }

    public function afterRemove(Entity $entity, RemoveOptions $options): void
    {
        if ($options->get(SaveOption::SILENT)) {
            return;
        }

        $this->processor->afterRemove($entity, $options);
    }

    public function afterRelate(
        Entity $entity,
        string $relationName,
        Entity $relatedEntity,
        array $columnData,
        RelateOptions $options
    ): void {

        if ($options->get(SaveOption::SILENT)) {
            return;
        }

        $this->processor->afterRelate($entity, $relatedEntity, $relationName, $options->toAssoc());
    }

    public function afterUnrelate(
        Entity $entity,
        string $relationName,
        Entity $relatedEntity,
        UnrelateOptions $options
    ): void {

        if ($options->get(SaveOption::SILENT)) {
            return;
        }

        $this->processor->afterUnrelate($entity, $relatedEntity, $relationName, $options->toAssoc());
    }
}
