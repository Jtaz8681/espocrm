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

namespace Espo\Tools\Lock;

use Espo\Core\Acl\AssignmentChecker\Helper;
use Espo\Core\Exceptions\Conflict;
use Espo\Core\Exceptions\ConflictSilent;
use Espo\Core\Exceptions\Error\Body;
use Espo\Core\Name\Field;
use Espo\Core\Utils\FieldUtil;
use Espo\Core\Utils\Language;
use Espo\Core\Utils\Log;
use Espo\ORM\Defs;
use Espo\ORM\Defs\Params\FieldParam;
use Espo\ORM\Entity;

class LockValidationHelper
{
    private const string PARAM_NOT_LOCKABLE = 'notLockable';

    /** @var string[] */
    private array $ignoreFieldList = [
        Field::MODIFIED_AT,
        Field::MODIFIED_BY,
        Field::IS_LOCKED,
        Field::STREAM_UPDATED_AT,
    ];

    public function __construct(
        private LockMetadataProvider $lockMetadataProvider,
        private FieldUtil $fieldUtil,
        private Defs $defs,
        private Log $log,
        private Language $language,
        private Helper $assignmentHelper,
    ) {}

    /**
     * @throws Conflict
     */
    public function validateBeforeSave(Entity $entity): void
    {
        if ($entity->isNew()) {
            return;
        }

        if (!$this->lockMetadataProvider->isEnabled($entity->getEntityType())) {
            return;
        }

        if (!$entity->getFetched(Field::IS_LOCKED)) {
            return;
        }

        $changedField = $this->getChangedLockedField($entity);

        if ($changedField === null) {
            return;
        }

        $this->log->info("Cannot modify a locked record, '{field}' cannot be changed.", ['field' => $changedField]);

        $fieldLabel = $this->language->translateLabel($changedField, 'fields', $entity->getEntityType());

        throw ConflictSilent::createWithBody(
            'cannotModifyLockedRecord',
            Body::create()->withMessageTranslation('cannotModifyLockedRecord', data: ['field' => $fieldLabel])
        );
    }

    /**
     * @throws Conflict
     */
    public function validateBeforeRemove(Entity $entity): void
    {
        if (!$this->lockMetadataProvider->isEnabled($entity->getEntityType())) {
            return;
        }

        if (!$entity->get(Field::IS_LOCKED)) {
            return;
        }

        throw ConflictSilent::createWithBody(
            'cannotRemoveLockedRecord',
            Body::create()->withMessageTranslation('cannotRemoveLockedRecord')
        );
    }

    private function getChangedLockedField(Entity $entity): ?string
    {
        $entityType = $entity->getEntityType();

        $entityDefs = $this->defs->getEntity($entityType);

        $ignoreFieldList = $this->getIgnoreFieldList($entityType);

        foreach ($entityDefs->getFieldList() as $fieldDefs) {
            $field = $fieldDefs->getName();

            if (
                $fieldDefs->getParam(FieldParam::READ_ONLY) ||
                $fieldDefs->getParam(FieldParam::READ_ONLY_AFTER_CREATE)
            ) {
                continue;
            }

            if (in_array($field, $ignoreFieldList)) {
                continue;
            }

            if ($fieldDefs->getParam(self::PARAM_NOT_LOCKABLE)) {
                continue;
            }

            foreach ($this->fieldUtil->getActualAttributeList($entityType, $field) as $attribute) {
                if ($entity->isAttributeChanged($attribute)) {
                    return $field;
                }
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getIgnoreFieldList(string $entityType): array
    {
        $ignoreFieldList = $this->ignoreFieldList;

        $entityDefs = $this->defs->getEntity($entityType);

        if ($this->assignmentHelper->hasCollaboratorsField($entityType)) {
            if ($this->assignmentHelper->hasAssignedUsersField($entityType)) {
                if (
                    $entityDefs->tryGetField(Field::ASSIGNED_USERS)
                        ?->getParam(self::PARAM_NOT_LOCKABLE)
                ) {
                    $ignoreFieldList[] = Field::COLLABORATORS;
                }
            } else if ($this->assignmentHelper->hasAssignedUserField($entityType)) {
                if (
                    $entityDefs->tryGetField(Field::ASSIGNED_USER)
                        ?->getParam(self::PARAM_NOT_LOCKABLE)
                ) {
                    $ignoreFieldList[] = Field::COLLABORATORS;
                }
            }
        }
        return $ignoreFieldList;
    }
}
