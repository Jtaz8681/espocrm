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

namespace Espo\Core\Utils;

use Espo\Core\ORM\EntityManager;
use Espo\Core\Mail\FiltersMatcher;
use Espo\Core\Utils\Config\SystemConfig;
use Espo\Entities\Email;
use Espo\Entities\EmailFilter;
use Espo\Entities\User;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\Part\Order;
use LogicException;
use stdClass;

/**
 * Looks for any matching Email Filter for a given email and user.
 */
class EmailFilterManager
{
    /** @var array<string, EmailFilter[]> */
    private array $data = [];
    private bool $useCache;

    private const CACHE_KEY = 'emailFilters';

    public function __construct(
        private EntityManager $entityManager,
        private FiltersMatcher $filtersMatcher,
        private DataCache $dataCache,
        SystemConfig $systemConfig,
    ) {
        $this->useCache = $systemConfig->useCache();
    }

    public function getMatchingFilter(Email $email, string $userId): ?EmailFilter
    {
        $filters = $this->get($userId);

        return $this->filtersMatcher->findMatch($email, $filters);
    }

    /**
     * @return EmailFilter[]
     */
    private function get(string $userId): array
    {
        if (array_key_exists($userId, $this->data)) {
            return $this->data[$userId];
        }

        $cacheKey = $this->composeCacheKey($userId);

        if ($this->useCache && $this->dataCache->has($cacheKey)) {
            $this->data[$userId] = $this->loadFromCache($cacheKey);

            return $this->data[$userId];
        }

        $this->data[$userId] = $this->fetch($userId);

        if ($this->useCache) {
            $this->storeToCache($userId);
        }

        return $this->data[$userId];
    }

    private function composeCacheKey(string $userId): string
    {
        return self::CACHE_KEY . '/' . $userId;
    }

    /**
     * @return EmailFilter[]
     */
    private function fetch(string $userId): array
    {
        $collection = $this->entityManager
            ->getRDBRepository(EmailFilter::ENTITY_TYPE)
            ->where([
                'parentId' => $userId,
                'parentType' => User::ENTITY_TYPE,
            ])
            ->order(
                Order::createByPositionInList(
                    Expression::column('action'),
                    [
                        EmailFilter::ACTION_SKIP,
                        EmailFilter::ACTION_MOVE_TO_FOLDER,
                        EmailFilter::ACTION_NONE,
                    ]
                )
            )
            ->find();

        return iterator_to_array($collection);
    }

    /**
     * @return EmailFilter[]
     */
    private function loadFromCache(string $cacheKey): array
    {
        /** @var stdClass[] $dataList */
        $dataList = $this->dataCache->get($cacheKey);

        /** @var EmailFilter[] $list */
        $list = [];

        foreach ($dataList as $item) {
            $entity = $this->entityManager->getNewEntity(EmailFilter::ENTITY_TYPE);

            $entity->set($item);
            $entity->setAsNotNew();

            $list[] = $entity;
        }

        return $list;
    }

    private function storeToCache(string $userId): void
    {
        if (!array_key_exists($userId, $this->data)) {
            throw new LogicException();
        }

        $dataList = [];

        foreach ($this->data[$userId] as $entity) {
            $dataList[] = $entity->getValueMap();
        }

        $cacheKey = $this->composeCacheKey($userId);

        $this->dataCache->store($cacheKey, $dataList);
    }
}
