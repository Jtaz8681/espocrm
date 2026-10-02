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

use Espo\Core\ORM\Entity;
use stdClass;

class Notification extends Entity
{
    public const ENTITY_TYPE = 'Notification';

    public const TYPE_ENTITY_REMOVED = 'EntityRemoved';
    public const TYPE_ASSIGN = 'Assign';
    public const TYPE_COLLABORATING = 'Collaborating';
    public const TYPE_EMAIL_RECEIVED = 'EmailReceived';
    public const TYPE_NOTE = 'Note';
    public const TYPE_MENTION_IN_POST = 'MentionInPost';
    public const TYPE_MESSAGE = 'Message';
    public const TYPE_USER_REACTION = 'UserReaction';
    public const TYPE_SYSTEM = 'System';

    public const ATTR_READ = 'read';
    public const ATTR_USER_ID = 'userId';
    public const ATTR_ACTION_ID = 'actionId';
    public const ATTR_NUMBER = 'number';

    public const string ATTR_RELATED_TYPE = 'relatedType';
    public const string ATTR_RELATED_ID = 'relatedId';
    public const string ATTR_RELATED_PARENT_TYPE = 'relatedParentType';
    public const string ATTR_RELATED_PARENT_ID = 'relatedParentId';

    public const string FIELD_DATA = 'data';
    public const string FIELD_MESSAGE = 'message';
    public const string FIELD_TYPE = 'type';
    public const string FIELD_RELATED_PARENT = 'relatedParent';

    /**
     * @since 10.0.0
     */
    public const string FIELD_RELATED = 'related';
    public const string FIELD_IS_FEATURED = 'isFeatured';

    public const string GROUP_TYPE_RECORD = 'Record';
    public const string GROUP_TYPE_EMAIL_RECEIVED = Notification::TYPE_EMAIL_RECEIVED;

    public const string DATE_ATTR_NOTE_ID = 'noteId';

    public function getType(): ?string
    {
        return $this->get('type');
    }

    public function setMessage(?string $message): self
    {
        $this->set('message', $message);

        return $this;
    }

    public function setType(string $type): self
    {
        $this->set('type', $type);

        return $this;
    }

    public function getData(): ?stdClass
    {
        return $this->get('data');
    }

    public function getUserId(): ?string
    {
        return $this->get('userId');
    }

    /**
     * @param stdClass|array<string, mixed> $data
     */
    public function setData(stdClass|array $data): self
    {
        $this->set('data', $data);

        return $this;
    }

    public function setUserId(string $userId): self
    {
        $this->set('userId', $userId);

        return $this;
    }

    public function getCreatedBy(): ?Link
    {
        /** @var ?Link */
        return $this->getValueObject('createdBy');
    }

    public function getRelated(): ?LinkParent
    {
        /** @var ?LinkParent */
        return $this->getValueObject('related');
    }

    /**
     * Note: 'relatedName' is not loaded for a performance reason. Pass 'relatedName' in 'data'.
     */
    public function setRelated(LinkParent|Entity|null $related): self
    {
        if ($related instanceof LinkParent) {
            $this->setValueObject('related', $related);

            return $this;
        }

        $this->relations->set('related', $related);

        return $this;
    }

    public function getRelatedParent(): ?LinkParent
    {
        /** @var ?LinkParent */
        return $this->getValueObject('relatedParent');
    }

    public function setRelatedParent(LinkParent|Entity|null $relatedParent): self
    {
        if ($relatedParent instanceof LinkParent) {
            $this->setValueObject('relatedParent', $relatedParent);

            return $this;
        }

        $this->relations->set('relatedParent', $relatedParent);

        return $this;
    }

    public function setRelatedType(?string $relatedType): self
    {
        $this->set('relatedType', $relatedType);

        return $this;
    }

    public function setRelatedId(?string $relatedId): self
    {
        $this->set('relatedId', $relatedId);

        return $this;
    }

    public function isRead(): bool
    {
        return $this->get('read');
    }

    public function setRead(bool $read = true): self
    {
        $this->set('read', $read);

        return $this;
    }

    /**
     * @since 9.2.0
     */
    public function setActionId(?string $actionId): self
    {
        $this->set('actionId', $actionId);

        return $this;
    }

    /**
     * @since 9.2.0
     */
    public function getActionId(): ?string
    {
        return $this->get('actionId');
    }

    /**
     * @internal
     */
    public function setGroupedCount(?int $groupedCount): self
    {
        return $this->set('groupedCount', $groupedCount);
    }

    /**
     * @internal
     */
    public function getGroupType(): ?string
    {
        return $this->get('groupType');
    }

    /**
     * @since 10.0.0
     */
    public function setIsFeatured(bool $isFeatured): self
    {
        return $this->set(self::FIELD_IS_FEATURED, $isFeatured);
    }
}
