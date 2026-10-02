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

namespace Espo\Core\FieldProcessing\Stream;

use Espo\Core\Name\Field;
use Espo\ORM\Entity;
use Espo\Core\Acl;
use Espo\Core\FieldProcessing\Loader as LoaderInterface;
use Espo\Core\FieldProcessing\Loader\Params;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Metadata;
use Espo\Entities\User;
use Espo\Tools\Stream\Service as StreamService;

/**
 * @implements LoaderInterface<Entity>
 */
class FollowersLoader implements LoaderInterface
{
    private const FOLLOWERS_LIMIT = 6;

    public function __construct(
        private StreamService $streamService,
        private Metadata $metadata,
        private User $user,
        private Acl $acl,
        private Config $config
    ) {}

    public function process(Entity $entity, Params $params): void
    {
        $this->processIsFollowed($entity);
        $this->processFollowers($entity);
    }

    public function processIsFollowed(Entity $entity): void
    {
        if (!$entity->hasAttribute(Field::IS_FOLLOWED)) {
            return;
        }

        $isFollowed = $this->streamService->checkIsFollowed($entity);

        $entity->set(Field::IS_FOLLOWED, $isFollowed);
    }

    public function processFollowers(Entity $entity): void
    {
        if ($this->user->isPortal()) {
            return;
        }

        if (!$this->metadata->get(['scopes', $entity->getEntityType(), 'stream'])) {
            return;
        }

        if (!$this->acl->checkEntityStream($entity)) {
            return;
        }

        $limit = $this->config->get('recordFollowersLoadLimit') ?? self::FOLLOWERS_LIMIT;

        $data = $this->streamService->getEntityFollowers($entity, 0, $limit);

        $entity->set(Field::FOLLOWERS . 'Ids', $data['idList']);
        $entity->set(Field::FOLLOWERS . 'Names', $data['nameMap']);
    }
}
