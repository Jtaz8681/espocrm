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

use Espo\Core\Acl;
use Espo\Core\Acl\Permission;
use Espo\Core\AclManager;
use Espo\ORM\EntityManager;
use Espo\Entities\User;
use Espo\Entities\Note;

use stdClass;

class NoteMentionHookProcessor
{
    public function __construct(
        private Service $service,
        private EntityManager $entityManager,
        private User $user,
        private Acl $acl,
        private AclManager $aclManager
    ) {}

    public function beforeSave(Note $note): void
    {
        if ($note->getType() !== Note::TYPE_POST) {
            return;
        }

        $this->process($note);
    }

    private function process(Note $note): void
    {
        $mentionData = (object) [];

        $previousMentionList = [];

        if (!$note->isNew()) {
            $previousMentionList = array_keys(get_object_vars($note->getData()->mentions ?? (object) []));
        }

        $matches = null;

        preg_match_all('/(@[\w@.-]+)/', $note->getPost() ?? '', $matches);

        $mentionCount = 0;

        if (!empty($matches[0]) && is_array($matches[0])) {
            $mentionCount = $this->processMatches($matches[0], $note, $mentionData, $previousMentionList);
        }

        $data = $note->getData();

        if ($mentionCount) {
            $data->mentions = $mentionData;
        } else {
            unset($data->mentions);
        }

        $note->setData($data);
    }

    /**
     * @param string[] $matchList
     * @param string[] $previousMentionList
     */
    private function processMatches(
        array $matchList,
        Note $note,
        stdClass $mentionData,
        array $previousMentionList
    ): int {

        $mentionCount = 0;

        $parent = $note->getParentId() && $note->getParentType() ?
            $this->entityManager->getEntityById($note->getParentType(), $note->getParentId()) :
            null;

        foreach ($matchList as $item) {
            $userName = substr($item, 1);

            $user = $this->entityManager
                ->getRDBRepositoryByClass(User::class)
                ->where([
                    'userName' => $userName,
                    'isActive' => true,
                ])
                ->findOne();

            if (!$user) {
                continue;
            }

            if (!$this->acl->checkUserPermission($user, Permission::MENTION)) {
                continue;
            }

            $mentionData->$item = (object) [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'userName' => $user->getUserName(),
                '_scope' => $user->getEntityType(),
            ];

            $mentionCount++;

            if (in_array($item, $previousMentionList)) {
                continue;
            }

            if ($user->getId() === $this->user->getId()) {
                continue;
            }

            if ($user->isPortal()) {
                continue;
            }

            if ($parent && !$this->aclManager->checkEntityStream($user, $parent)) {
                continue;
            }

            $note->addNotifiedUserId($user->getId());

            $this->service->notifyAboutMentionInPost($user->getId(), $note);
        }

        return $mentionCount;
    }
}
