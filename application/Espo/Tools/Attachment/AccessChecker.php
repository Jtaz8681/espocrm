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

namespace Espo\Tools\Attachment;

use Espo\Core\Acl;
use Espo\Core\Acl\Table;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Metadata;
use Espo\Entities\Attachment;
use Espo\Entities\Settings;
use Espo\Entities\User;
use Espo\Core\ORM\Type\FieldType;

class AccessChecker
{
    /** @var string[] */
    private $adminOnlyHavingInlineAttachmentsEntityTypeList = ['TemplateManager'];

    /** @var string[] */
    private $attachmentFieldTypeList = [
        FieldType::FILE,
        FieldType::IMAGE,
        FieldType::ATTACHMENT_MULTIPLE,
    ];

    /** @var string[] */
    private $inlineAttachmentFieldTypeList = [
        FieldType::WYSIWYG,
    ];

    /** @var string[] */
    private $allowedRoleList = [
        Attachment::ROLE_ATTACHMENT,
        Attachment::ROLE_INLINE_ATTACHMENT,
    ];

    public function __construct(
        private User $user,
        private Acl $acl,
        private Metadata $metadata
    ) {}

    /**
     * Check access to a field and role allowance.
     *
     * @throws Forbidden
     */
    public function check(FieldData $fieldData, string $role = Attachment::ROLE_ATTACHMENT): void
    {
        if (!in_array($role, $this->allowedRoleList)) {
            throw new Forbidden("Role not allowed.");
        }

        $relatedEntityType = $fieldData->getParentType() ?? $fieldData->getRelatedType();
        $field = $fieldData->getField();

        if (!$relatedEntityType) {
            throw new Forbidden();
        }

        if (
            $this->user->isAdmin() &&
            $role === Attachment::ROLE_INLINE_ATTACHMENT &&
            in_array($relatedEntityType, $this->adminOnlyHavingInlineAttachmentsEntityTypeList)
        ) {
            return;
        }

        $fieldType = $this->metadata->get(['entityDefs', $relatedEntityType, 'fields', $field, 'type']);

        if (!$fieldType) {
            throw new Forbidden("Field '$field' does not exist.");
        }

        $fieldTypeList = $role === Attachment::ROLE_INLINE_ATTACHMENT ?
            $this->inlineAttachmentFieldTypeList :
            $this->attachmentFieldTypeList;

        if (!in_array($fieldType, $fieldTypeList)) {
            throw new Forbidden("Field type '$fieldType' is not allowed for $role.");
        }

        if ($this->user->isAdmin() && $relatedEntityType === Settings::ENTITY_TYPE) {
            return;
        }

        if (
            !$this->acl->checkScope($relatedEntityType, Table::ACTION_CREATE) &&
            !$this->acl->checkScope($relatedEntityType, Table::ACTION_EDIT)
        ) {
            throw new Forbidden("No access to " . $relatedEntityType . ".");
        }

        if (!$this->acl->checkField($relatedEntityType, $field, Table::ACTION_EDIT)) {
            throw new Forbidden("No access to field '$field'.");
        }
    }
}
