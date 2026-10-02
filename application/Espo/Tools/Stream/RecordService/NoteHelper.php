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

namespace Espo\Tools\Stream\RecordService;

use Espo\Core\Name\Field;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Utils\FieldUtil;
use Espo\Entities\Note;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use stdClass;

class NoteHelper
{
    public function __construct(
        private EntityManager $entityManager,
        private FieldUtil $fieldUtil
    ) {}

    public function prepare(Note $note): void
    {
        if ($note->getType() === Note::TYPE_UPDATE) {
            $this->prepareNoteUpdate($note);
        }
    }

    private function prepareNoteUpdate(Note $note): void
    {
        $data = $note->getData();

        /** @var ?string[] $fieldList */
        $fieldList = $data->fields ?? null;
        $attributes = $data->attributes ?? null;

        if (!$attributes instanceof stdClass) {
            return;
        }

        $was = $attributes->was ?? null;

        if (!$was instanceof stdClass) {
            return;
        }

        if (!is_array($fieldList)) {
            return;
        }

        foreach ($fieldList as $field) {
            if ($this->loadNoteUpdateWasForField($note, $field, $was)) {
                $note->setData($data);
            }
        }
    }

    private function loadNoteUpdateWasForField(Note $note, string $field, stdClass $was): bool
    {
        if (!$note->getParentType() || !$note->getParentId()) {
            return false;
        }

        $type = $this->fieldUtil->getFieldType($note->getParentType(), $field);

        if ($type === FieldType::LINK_MULTIPLE) {
            $this->loadNoteUpdateWasForFieldLinkMultiple($note, $field, $was);

            return true;
        }

        return false;
    }

    private function loadNoteUpdateWasForFieldLinkMultiple(Note $note, string $field, stdClass $was): void
    {
        /** @var ?string[] $ids */
        $ids = $was->{$field . 'Ids'} ?? null;

        $names = (object) [];

        if (!is_array($ids)) {
            return;
        }

        $entityType = $note->getParentType();

        if (!$entityType) {
            return;
        }

        $foreignEntityType = $this->entityManager
            ->getDefs()
            ->getEntity($entityType)
            ->tryGetRelation($field)
            ?->tryGetForeignEntityType();

        if (!$foreignEntityType) {
            return;
        }

        $collection = $this->entityManager
            ->getRDBRepository($foreignEntityType)
            ->select([Attribute::ID, Field::NAME])
            ->where([Attribute::ID => $ids])
            ->find();

        foreach ($collection as $entity) {
            $names->{$entity->getId()} = $entity->get(Field::NAME);
        }

        $was->{$field . 'Names'} = $names;
    }
}
