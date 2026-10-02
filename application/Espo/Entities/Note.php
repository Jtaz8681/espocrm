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

namespace Espo\Entities;

use Espo\Core\Field\Link;
use Espo\Core\Field\LinkParent;
use Espo\Core\Name\Field;
use Espo\Core\ORM\Entity;
use Espo\Core\Field\DateTime;
use Espo\ORM\Entity as OrmEntity;
use Espo\ORM\EntityCollection;
use Espo\ORM\Name\Attribute;

use RuntimeException;
use stdClass;

class Note extends Entity
{
    public const ENTITY_TYPE = 'Note';

    public const TARGET_SELF = 'self';
    public const TARGET_ALL = 'all';
    public const TARGET_TEAMS = 'teams';
    public const TARGET_USERS = 'users';
    public const TARGET_PORTALS = 'portals';

    public const TYPE_POST = 'Post';
    public const TYPE_UPDATE = 'Update';
    /**
     * @deprecated As of v9.2.0
     */
    public const TYPE_STATUS = 'Status';
    public const TYPE_CREATE = 'Create';
    public const TYPE_CREATE_RELATED = 'CreateRelated';
    public const TYPE_RELATE = 'Relate';
    public const TYPE_UNRELATE = 'Unrelate';
    public const TYPE_ASSIGN = 'Assign';
    public const TYPE_EMAIL_RECEIVED = 'EmailReceived';
    public const TYPE_EMAIL_SENT = 'EmailSent';

    public const string DATA_ATTR_ADDED_ASSIGNED_USERS = 'addedAssignedUsers';
    public const string DATA_ATTR_REMOVED_ASSIGNED_USERS = 'removedAssignedUsers';
    public const string DATA_ATTR_ASSIGNED_USERS = 'assignedUsers';
    public const string DATA_ATTR_STATUS_VALUE = 'statusValue';
    public const string DATA_ATTR_STATUS_FIELD = 'statusField';
    public const string DATA_ATTR_ASSIGNED_USER_ID = 'assignedUserId';
    public const string DATA_ATTR_ASSIGNED_USER_NAME = 'assignedUserName';
    public const string DATA_ATTR_FIELDS = 'fields';
    public const string DATA_ATTR_ATTRIBUTES = 'attributes';
    public const string DATA_ATTR_PERSON_TYPE = 'personEntityType';
    public const string DATA_ATTR_PERSON_NAME = 'personEntityName';
    public const string DATA_ATTR_PERSON_ID = 'personEntityId';
    public const string DATA_ATTR_FROM_STRING = 'fromString';

    public const string FIELD_TYPE = 'type';
    public const string FIELD_NUMBER = 'number';
    public const string FIELD_POST = 'post';
    public const string FIELD_IS_INTERNAL = 'isInternal';
    public const string FIELD_IS_PINNED = 'isPinned';

    /**
     * @since 10.0.0
     */
    public const string FIELD_RELATED = 'related';

    private bool $aclIsProcessed = false;

    public function isPost(): bool
    {
        return $this->getType() === self::TYPE_POST;
    }

    public function isGlobal(): bool
    {
        return (bool) $this->get('isGlobal');
    }

    public function getType(): ?string
    {
        return $this->get('type');
    }

    public function getTargetType(): ?string
    {
        return $this->get('targetType');
    }

    public function getParentType(): ?string
    {
        return $this->get('parentType');
    }

    public function getParentId(): ?string
    {
        return $this->get('parentId');
    }

    public function getSuperParentType(): ?string
    {
        return $this->get('superParentType');
    }

    public function getSuperParentId(): ?string
    {
        return $this->get('superParentId');
    }

    public function getRelatedType(): ?string
    {
        return $this->get('relatedType');
    }

    public function getRelatedId(): ?string
    {
        return $this->get('relatedId');
    }

    public function getData(): stdClass
    {
        return $this->get('data') ?? (object) [];
    }

    public function isInternal(): bool
    {
        return (bool) $this->get(self::FIELD_IS_INTERNAL);
    }

    public function getPost(): ?string
    {
        return $this->get('post');
    }

    public function getNumber(): int
    {
        return $this->get('number');
    }

    public function getCreatedAt(): ?DateTime
    {
        /** @var ?DateTime */
        return $this->getValueObject(Field::CREATED_AT);
    }

    public function setAclIsProcessed(): void
    {
        $this->aclIsProcessed = true;
    }

    public function isAclProcessed(): bool
    {
        return $this->aclIsProcessed;
    }

    public function loadAttachments(): void
    {
        $data = $this->get('data');

        if (empty($data) || empty($data->attachmentsIds) || !is_array($data->attachmentsIds)) {
            $this->loadLinkMultipleField('attachments');

            return;
        }

        if (!$this->entityManager) {
            throw new RuntimeException();
        }

        $attachmentsIds = $data->attachmentsIds;

        $collection = $this->entityManager
            ->getRDBRepository(Attachment::ENTITY_TYPE)
            ->select([Attribute::ID, Field::NAME, 'type'])
            ->order(Field::CREATED_AT)
            ->where([
                Attribute::ID => $attachmentsIds
            ])
            ->find();

        $ids = [];

        $names = (object) [];
        $types = (object) [];

        foreach ($collection as $e) {
            $id = $e->getId();

            $ids[] = $id;

            $names->$id = $e->get(Field::NAME);
            $types->$id = $e->get('type');
        }

        $this->set('attachmentsIds', $ids);
        $this->set('attachmentsNames', $names);
        $this->set('attachmentsTypes', $types);
    }

    public function addNotifiedUserId(string $userId): void
    {
        $userIdList = $this->get('notifiedUserIdList');

        if (!is_array($userIdList)) {
            $userIdList = [];
        }

        if (!in_array($userId, $userIdList)) {
            $userIdList[] = $userId;
        }

        $this->set('notifiedUserIdList', $userIdList);
    }

    public function isUserIdNotified(string $userId): bool
    {
        $userIdList = $this->get('notifiedUserIdList') ?? [];

        return in_array($userId, $userIdList);
    }

    public function getCreatedById(): ?string
    {
        return $this->get('createdById');
    }

    public function setType(string $type): self
    {
        return $this->set('type', $type);
    }

    public function setParent(LinkParent|OrmEntity|null $parent): self
    {
        return $this->setRelatedLinkOrEntity(Field::PARENT, $parent);
    }

    public function setRelated(LinkParent|OrmEntity|null $related): self
    {
        return $this->setRelatedLinkOrEntity('related', $related);
    }

    public function setSuperParent(LinkParent|OrmEntity|null $superParent): self
    {
        if ($superParent instanceof LinkParent) {
            $this->setMultiple([
                'superParentId' => $superParent->getId(),
                'superParentType' => $superParent->getEntityType(),
            ]);

            return $this;
        }

        return $this->setRelatedLinkOrEntity('superParent', $superParent);
    }

    public function setPost(?string $post): self
    {
        $this->set('post', $post);

        return $this;
    }

    /**
     * @param stdClass|array<string, mixed> $data
     */
    public function setData(stdClass|array $data): self
    {
        return $this->set('data', $data);
    }

    public function loadAdditionalFields(): void
    {
        if (
            $this->getType() == self::TYPE_POST ||
            $this->getType() == self::TYPE_EMAIL_RECEIVED ||
            $this->getType() == self::TYPE_EMAIL_SENT
        ) {
            $this->loadAttachments();
        }

        if ($this->getParentId() && $this->getParentType()) {
            $this->loadParentNameField(Field::PARENT);
        }

        if ($this->getRelatedId() && $this->getRelatedType()) {
            $this->loadParentNameField('related');
        }

        if (
            $this->getType() == self::TYPE_POST &&
            $this->getParentId() === null &&
            !$this->get('isGlobal')
        ) {
            $targetType = $this->getTargetType();

            if (
                !$targetType ||
                $targetType === self::TARGET_USERS ||
                $targetType === self::TARGET_SELF
            ) {
                $this->loadLinkMultipleField('users');
            }

            if (
                $targetType !== self::TARGET_USERS &&
                $targetType !== self::TARGET_SELF
            ) {
                if (!$targetType || $targetType === self::TARGET_TEAMS) {
                    $this->loadLinkMultipleField(Field::TEAMS);
                } else if ($targetType === self::TARGET_PORTALS) {
                    $this->loadLinkMultipleField('portals');
                }
            }
        }
    }

    /**
     * @param string[] $ids
     */
    public function setTeamsIds(array $ids): self
    {
        return $this->set('teamsIds', $ids);
    }

    /**
     * @param string[] $ids
     */
    public function setUsersIds(array $ids): self
    {
        return $this->set('usersIds', $ids);
    }

    public function isPinned(): bool
    {
        return (bool) $this->get('isPinned');
    }

    public function setIsPinned(bool $isPinned): self
    {
        $this->set('isPinned', $isPinned);

        return $this;
    }

    /**
     * @since 10.0.0
     */
    public function setIsInternal(bool $isInternal): self
    {
        return $this->set(self::FIELD_IS_INTERNAL, $isInternal);
    }

    public function getParent(): ?OrmEntity
    {
        return $this->relations->getOne(Field::PARENT);
    }

    public function getSuperParent(): ?OrmEntity
    {
        return $this->relations->getOne('superParent');
    }

    /**
     * @return EntityCollection<Attachment>
     */
    public function getAttachments(): EntityCollection
    {
        /** @var EntityCollection<Attachment> */
        return $this->relations->getMany('attachments');
    }

    public function getCreatedBy(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject('createdBy');
    }
}
