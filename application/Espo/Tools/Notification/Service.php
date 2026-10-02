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

namespace Espo\Tools\Notification;

use Espo\Core\Field\LinkParent;
use Espo\Core\Name\Field;
use Espo\Core\Utils\Id\RecordIdGenerator;
use Espo\Entities\Note;
use Espo\Entities\Notification;
use Espo\Entities\User;
use Espo\Entities\Email;
use Espo\Core\AclManager;
use Espo\Core\WebSocket\Submission;
use Espo\Core\Utils\DateTime as DateTimeUtil;
use Espo\Modules\Crm\Entities\CaseObj;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\Tools\Notification\HookProcessor\Params;
use stdClass;

class Service
{
    public function __construct(
        private EntityManager $entityManager,
        private AclManager $aclManager,
        private Submission $webSocketSubmission,
        private RecordIdGenerator $idGenerator,
    ) {}

    public function notifyAboutMentionInPost(string $userId, Note $note): void
    {
        $notification = $this->entityManager->getRDBRepositoryByClass(Notification::class)->getNew();

        $notification
            ->setType(Notification::TYPE_MENTION_IN_POST)
            ->setData(['noteId' => $note->getId()])
            ->setUserId($userId)
            ->setRelated(LinkParent::fromEntity($note));

        $this->entityManager->saveEntity($notification);
    }

    /**
     * @param string[] $userIdList
     * @param ?Params $params Hook parameters. As of v9.2.0.
     * @param ?NoteNotifyParams $notifyParams Parameters. As of v10.0.1.
     */
    public function notifyAboutNote(
        array $userIdList,
        Note $note,
        ?Params $params = null,
        ?NoteNotifyParams $notifyParams = null,
    ): void {
        $related = null;

        if ($note->getRelatedType() === Email::ENTITY_TYPE) {
            $related = $this->entityManager
                ->getRDBRepository(Email::ENTITY_TYPE)
                ->select([
                    Attribute::ID,
                    'sentById',
                    'createdById',
                ])
                ->where([Attribute::ID => $note->getRelatedId()])
                ->findOne();
        }

        $now = date(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);

        $collection = $this->entityManager->getCollectionFactory()->create();

        $users = $this->entityManager
            ->getRDBRepositoryByClass(User::class)
            ->select([
                Attribute::ID,
                User::ATTR_TYPE,
            ])
            ->where([
                User::ATTR_IS_ACTIVE => true,
                Attribute::ID => $userIdList,
            ])
            ->find();

        foreach ($users as $user) {
            if (!$this->checkUserNoteAccess($user, $note)) {
                continue;
            }

            if ($note->getCreatedById() === $user->getId()) {
                continue;
            }

            if (
                $related instanceof Email &&
                $related->getSentBy()?->getId() === $user->getId()
            ) {
                continue;
            }

            if ($related && $related->get('createdById') === $user->getId()) {
                continue;
            }

            $actionId = $params?->actionId;

            $isFeatured = false;

            if ($this->isUserTargetAssignee($note, $user)) {
                $isFeatured = true;

                // Do not group notifications about assignment.
                $actionId = null;
            }

            $relatedParent = $note->getParentType() && $note->getParentId() ?
                LinkParent::create($note->getParentType(), $note->getParentId()) : null;

            if ($notifyParams?->isSuper) {
                $relatedParent = $note->getSuperParentType() && $note->getSuperParentId() ?
                    LinkParent::create($note->getSuperParentType(), $note->getSuperParentId()) : null;
            }

            $notification = $this->entityManager->getRDBRepositoryByClass(Notification::class)->getNew();

            $notification
                ->set(Attribute::ID, $this->idGenerator->generate())
                ->set(Field::CREATED_AT, $now)
                ->setData([Notification::DATE_ATTR_NOTE_ID => $note->getId()])
                ->setType(Notification::TYPE_NOTE)
                ->setUserId($user->getId())
                ->setRelated(LinkParent::fromEntity($note))
                ->setRelatedParent($relatedParent)
                ->setActionId($actionId)
                ->setIsFeatured($isFeatured);

            $collection[] = $notification;
        }

        if (!count($collection)) {
            return;
        }

        $this->entityManager->getMapper()->massInsert($collection);

        foreach ($userIdList as $userId) {
            $this->webSocketSubmission->submit('newNotification', $userId);
        }
    }

    private function checkUserNoteAccess(User $user, Note $note): bool
    {
        if ($user->isPortal()) {
            if ($note->getRelatedType()) {
                /** @todo Revise. */
                return
                    $note->getRelatedType() === Email::ENTITY_TYPE &&
                    $note->getParentType() === CaseObj::ENTITY_TYPE;
            }

            return true;
        }

        if ($note->getRelatedType() && !$this->aclManager->checkScope($user, $note->getRelatedType())) {
            return false;
        }

        if ($note->getParentType() && !$this->aclManager->checkScope($user, $note->getParentType())) {
            return false;
        }

        return true;
    }


    private function isUserTargetAssignee(Note $note, User $user): bool
    {
        if (!in_array($note->getType(), [Note::TYPE_ASSIGN, Note::TYPE_CREATE])) {
            return false;
        }

        $noteData = $note->getData();

        if (($noteData->{Note::DATA_ATTR_ASSIGNED_USER_ID} ?? null) === $user->getId()) {
            return true;
        }

        $assignedUsers = $noteData->{Note::DATA_ATTR_ASSIGNED_USERS} ??
            $noteData->{Note::DATA_ATTR_ADDED_ASSIGNED_USERS} ?? null;

        if (!is_array($assignedUsers)) {
            return false;
        }

        foreach ($assignedUsers as $item) {
            if (!$item instanceof stdClass) {
                continue;
            }

            if (($item->{Attribute::ID} ?? null) === $user->getId()) {
                return true;
            }
        }

        return false;
    }
}
