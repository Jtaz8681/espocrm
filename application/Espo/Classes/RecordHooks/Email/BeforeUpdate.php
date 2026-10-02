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

namespace Espo\Classes\RecordHooks\Email;

use Espo\Core\Mail\EmailSender;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Type\FieldType;
use Espo\Core\Record\Hook\SaveHook;
use Espo\Core\Utils\FieldUtil;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\SystemUser;
use Espo\Entities\Email;
use Espo\Entities\User;
use Espo\ORM\Entity;
use Espo\ORM\EntityManager;

/**
 * @implements SaveHook<Email>
 */
class BeforeUpdate implements SaveHook
{
    /** @var string[] */
    private $allowedForUpdateFieldList = [
        Field::PARENT,
        Field::TEAMS,
        Field::ASSIGNED_USER,
    ];

    public function __construct(
        private User $user,
        private EntityManager $entityManager,
        private FieldUtil $fieldUtil,
        private Metadata $metadata,
    ) {}

    public function process(Entity $entity): void
    {
        $skipFilter = false;

        if ($this->user->isAdmin()) {
            $skipFilter = true;
        }

        if ($this->isEmailManuallyArchived($entity)) {
            $skipFilter = true;
        } else if ($entity->isAttributeChanged('dateSent')) {
            $entity->set('dateSent', $entity->getFetched('dateSent'));
        }

        if ($entity->getStatus() === Email::STATUS_DRAFT) {
            $skipFilter = true;
        }

        if (
            $entity->getStatus() === Email::STATUS_SENDING &&
            $entity->getFetched('status') === Email::STATUS_DRAFT
        ) {
            $skipFilter = true;
        }

        if (
            $entity->isAttributeChanged('status') &&
            $entity->getFetched('status') === Email::STATUS_ARCHIVED
        ) {
            $entity->setStatus(Email::STATUS_ARCHIVED);
        }

        if (!$skipFilter) {
            $this->clearEntityForUpdate($entity);
        }

        if ($entity->getStatus() == Email::STATUS_SENDING) {
            $messageId = EmailSender::generateMessageId($entity);

            $entity->setMessageId('<' . $messageId . '>');
        }
    }

    private function isEmailManuallyArchived(Email $email): bool
    {
        if ($email->getStatus() !== Email::STATUS_ARCHIVED) {
            return false;
        }

        $userId = $email->getCreatedBy()?->getId();

        if (!$userId) {
            return false;
        }

        $user = $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->getById($userId);

        if (!$user) {
            return true;
        }

        return $user->getUserName() !== SystemUser::NAME;
    }

    private function clearEntityForUpdate(Email $email): void
    {
        $entityDefs = $this->entityManager
            ->getDefs()
            ->getEntity(Email::ENTITY_TYPE);

        foreach ($entityDefs->getFieldList() as $fieldDefs) {
            $field = $fieldDefs->getName();

            if ($fieldDefs->getParam('isCustom')) {
                continue;
            }

            if (
                $fieldDefs->getType() === FieldType::LINK_MULTIPLE &&
                $this->metadata->get("entityDefs.Email.links.$field.isCustom")
            ) {
                continue;
            }

            if (in_array($field, $this->allowedForUpdateFieldList)) {
                continue;
            }

            $attributeList = $this->fieldUtil->getAttributeList(Email::ENTITY_TYPE, $field);

            foreach ($attributeList as $attribute) {
                if ($email->isAttributeChanged($attribute) && $email->isAttributeWritten($attribute)) {
                    $email->set($attribute, $email->getFetched($attribute));
                }
            }
        }
    }
}
