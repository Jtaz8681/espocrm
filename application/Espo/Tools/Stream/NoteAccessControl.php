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

namespace Espo\Tools\Stream;

use Espo\Core\Utils\Metadata;
use Espo\Entities\Note;
use Espo\Entities\User;
use Espo\Core\Utils\Acl\UserAclManagerProvider;

class NoteAccessControl
{
    public function __construct(
        private UserAclManagerProvider $userAclManagerProvider,
        private Metadata $metadata,
    ) {}

    public function apply(Note $note, User $user): void
    {
        if ($note->getType() === Note::TYPE_UPDATE && $note->getParentType()) {
            $data = $note->getData();

            $fields = $data->fields ?? [];

            $data->attributes = $data->attributes ?? (object) [];
            $data->attributes->was = $data->attributes->was ?? (object) [];
            $data->attributes->became = $data->attributes->became ?? (object) [];

            $forbiddenFieldList = $this->userAclManagerProvider
                ->get($user)
                ->getScopeForbiddenFieldList($user, $note->getParentType());

            $aclManager = $this->userAclManagerProvider->get($user);

            $forbiddenAttributeList = $aclManager->getScopeForbiddenAttributeList($user, $note->getParentType());

            $data->fields = array_values(array_diff($fields, $forbiddenFieldList));

            foreach ($forbiddenAttributeList as $attribute) {
                unset($data->attributes->was->$attribute);
                unset($data->attributes->became->$attribute);
            }

            $statusField = $this->metadata->get("scopes.{$note->getParentType()}.statusField");

            if (
                $statusField &&
                !$aclManager->checkField($user, $note->getParentType(), $statusField)
            ) {
                unset($data->value);
            }

            $note->setData($data);
        }

        if ($note->getType() === Note::TYPE_CREATE && $note->getParentType()) {
            $forbiddenFieldList = $this->userAclManagerProvider
                ->get($user)
                ->getScopeForbiddenFieldList($user, $note->getParentType());

            $data = $note->getData();

            $field = $data->statusField ?? null;

            if (in_array($field, $forbiddenFieldList)) {
                $data->statusValue = null;
                $data->statusStyle = null; // Legacy.
            }

            $note->setData($data);
        }
    }
}
