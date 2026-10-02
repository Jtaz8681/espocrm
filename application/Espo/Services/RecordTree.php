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

namespace Espo\Services;

use Espo\Core\Acl\Table;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Name\Field;
use Espo\Core\Record\LinkResult;
use Espo\Core\Record\UpdateResult;
use Espo\Core\Templates\Entities\CategoryTree;
use Espo\ORM\Collection;
use Espo\ORM\Entity;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Order;
use Espo\Core\Acl\Table as AclTable;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\UpdateParams;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\Where\Item as WhereItem;
use Espo\Core\Acl\Exceptions\NotImplemented;

use ArrayAccess;
use Espo\Tools\CategoryTree\Move\LoopReferenceChecker;
use Espo\Tools\CategoryTree\Record\ReadTreeParams;
use stdClass;

/**
 * @template TEntity of Entity
 * @extends Record<TEntity>
 */
class RecordTree extends Record
{
    private const MAX_DEPTH = 2;

    private ?Entity $seed = null;

    /**
     * @var ?string
     * @todo Add native type in v9.3.
     */
    protected $subjectEntityType = null;

    /**
     * @var ?string
     * @todo Add native type in v9.3.
     */
    protected $categoryField = null;

    /**
     * @return ?Collection<Entity>
     * @throws Forbidden
     * @throws BadRequest
     */
    public function getTree(ReadTreeParams $params): ?Collection
    {
        if (!$this->acl->check($this->entityType, Table::ACTION_READ)) {
            throw new Forbidden();
        }

        $path = $params->currentId ? $this->getTreeItemPath($params->currentId) : null;

        return $this->getTreeInternal($params->parentId, $params, $path);
    }

    /**
     * @param string[] $path
     * @return ?Collection<Entity>
     * @throws BadRequest
     * @throws Forbidden
     */
    private function getTreeInternal(?string $parentId, ReadTreeParams $params, ?array $path, int $level = 0): ?Collection
    {
        $maxDepth = $params->maxDepth ?? self::MAX_DEPTH;

        if ($level === $maxDepth) {
            if ($path === null || !in_array($parentId, $path)) {
                return null;
            }
        }

        $searchParams = SearchParams::create();

        if ($params->where) {
            $searchParams = $searchParams->withWhere($params->where);
        }

        $selectBuilder = $this->selectBuilderFactory
            ->create()
            ->from($this->entityType)
            ->withStrictAccessControl()
            ->withSearchParams($searchParams)
            ->buildQueryBuilder()
            ->where(['parentId' => $parentId]);

        $selectBuilder->order([]);

        if ($this->hasOrder()) {
            $selectBuilder->order('order', Order::ASC);
        }

        $selectBuilder->order(Field::NAME, Order::ASC);

        $filterItems = false;

        if ($this->checkFilterOnlyNotEmpty()) {
            $filterItems = true;
        }

        $collection = $this->getRepository()
            ->clone($selectBuilder->build())
            ->find();

        if (
            ($params->onlyNotEmpty || $filterItems) &&
            $collection instanceof ArrayAccess
        ) {
            foreach ($collection as $i => $entity) {
                if ($this->checkItemIsEmpty($entity)) {
                    unset($collection[$i]);
                }
            }
        }

        foreach ($collection as $entity) {
            $childList = $this->getTreeInternal($entity->getId(), $params, $path, $level + 1);

            $entity->set('childList', $childList?->getValueMapList());
        }

        return $collection;
    }

    protected function checkFilterOnlyNotEmpty(): bool
    {
        try {
            if (!$this->acl->checkScope($this->getSubjectEntityType(), Table::ACTION_CREATE)) {
                return true;
            }
        } catch (NotImplemented) {
            return false;
        }

        return false;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    protected function checkItemIsEmpty(Entity $entity): bool
    {
        $entityType = $this->getSubjectEntityType();

        // If used without an actual subject entity.
        if (!$this->entityManager->hasRepository($entityType)) {
            return true;
        }

        $query = $this->selectBuilderFactory
            ->create()
            ->from($entityType)
            ->withStrictAccessControl()
            ->withWhere(
                WhereItem::fromRaw([
                    'type' => 'inCategory',
                    'attribute' => $this->getCategoryField(),
                    'value' => $entity->getId(),
                ])
            )
            ->build();

        $one = $this->entityManager
            ->getRDBRepository($entityType)
            ->clone($query)
            ->select([Attribute::ID])
            ->findOne();

        if ($one) {
            return false;
        }

        return true;
    }

    /**
     * @throws Forbidden
     * @throws NotFound
     */
    public function getCategoryData(?string $id): ?stdClass
    {
        if (!$this->acl->check($this->entityType, AclTable::ACTION_READ)) {
            throw new Forbidden();
        }

        if ($id === null) {
            return null;
        }

        $category = $this->entityManager->getEntityById($this->entityType, $id);

        if (!$category) {
            throw new NotFound();
        }

        if (!$this->acl->check($category, AclTable::ACTION_READ)) {
            throw new Forbidden();
        }

        return (object) [
            'upperId' => $category->get('parentId'),
            'upperName' => $category->get('parentName'),
            'id' => $id,
            'name' => $category->get(Field::NAME),
        ];
    }

    /**
     * @return string[]
     * @throws Forbidden
     */
    public function getTreeItemPath(?string $parentId = null): array
    {
        if (!$this->acl->check($this->entityType, AclTable::ACTION_READ)) {
            throw new Forbidden();
        }

        $arr = [];

        while (1) {
            if (empty($parentId)) {
                break;
            }

            $parent = $this->entityManager->getEntityById($this->entityType, $parentId);

            if ($parent) {
                $parentId = $parent->get('parentId');

                array_unshift($arr, $parent->getId());
            } else {
                $parentId = null;
            }
        }

        return $arr;
    }

    private function getSeed(): Entity
    {
        if (empty($this->seed)) {
            $this->seed = $this->entityManager->getNewEntity($this->entityType);
        }

        return $this->seed;
    }

    private function hasOrder(): bool
    {
        $seed = $this->getSeed();

        if ($seed->hasAttribute('order')) {
            return true;
        }

        return false;
    }

    /**
     * @throws Forbidden
     * @throws Error
     * @todo Refactor.
     */
    protected function beforeCreateEntity(Entity $entity, stdClass $data): void
    {
        parent::beforeCreateEntity($entity, $data);

        if (!empty($data->parentId)) {
            $parent = $this->entityManager->getEntityById($this->entityType, $data->parentId);

            if (!$parent) {
                throw new Error("Tried to create tree item entity with not existing parent.");
            }

            if (!$this->acl->check($parent, Table::ACTION_EDIT)) {
                throw new Forbidden();
            }
        }
    }

    /**
     * @throws Forbidden
     * @throws BadRequest
     */
    protected function beforeDeleteEntity(Entity $entity): void
    {
        parent::beforeDeleteEntity($entity);

        $childCategory = $this->entityManager
            ->getRelation($entity, 'children')
            ->findOne();

        if ($childCategory) {
            throw Forbidden::createWithBody(
                'cannotRemoveCategoryWithChildCategory',
                Error\Body::create()->withMessageTranslation('cannotRemoveCategoryWithChildCategory')
            );
        }

        if (!$this->checkItemIsEmpty($entity)) {
            throw Forbidden::createWithBody(
                'cannotRemoveNotEmptyCategory',
                Error\Body::create()->withMessageTranslation('cannotRemoveNotEmptyCategory')
            );
        }
    }

    /**
     * @throws Forbidden
     */
    protected function beforeUpdateEntity(Entity $entity, stdClass $data): void
    {
        parent::beforeUpdateEntity($entity, $data);

        if (
            !$entity->isNew() &&
            $entity->isAttributeChanged('parentId') &&
            $entity->get('parentId') &&
            $entity instanceof CategoryTree
        ) {
            $parentId = $entity->get('parentId');

            $parent = $this->entityManager->getEntityById($this->entityType, $parentId);

            if ($parent) {
                $this->injectableFactory->create(LoopReferenceChecker::class)
                    ->check($entity, $parent);
            }
        }
    }

    public function update(string $id, stdClass $data, UpdateParams $params = new UpdateParams()): UpdateResult
    {
        if (!empty($data->parentId) && $data->parentId === $id) {
            throw new Forbidden();
        }

        return parent::update($id, $data, $params);
    }

    public function link(string $id, string $link, string $foreignId): LinkResult
    {
        if ($id == $foreignId) {
            throw new Forbidden();
        }

        return parent::link($id, $link, $foreignId);
    }

    /**
     * @return string[]
     * @throws Forbidden
     * @throws BadRequest
     */
    public function getLastChildrenIdList(?string $parentId = null): array
    {
        if (!$this->acl->check($this->entityType, Table::ACTION_READ)) {
            throw new Forbidden();
        }

        $query = $this->selectBuilderFactory
            ->create()
            ->from($this->entityType)
            ->withStrictAccessControl()
            ->buildQueryBuilder()
            ->where([
                'parentId' => $parentId,
            ])
            ->build();

        $idList = [];

        $includingRecords = false;

        if ($this->checkFilterOnlyNotEmpty()) {
            $includingRecords = true;
        }

        $collection = $this->getRepository()
            ->clone($query)
            ->select([Attribute::ID])
            ->find();

        foreach ($collection as $entity) {
            $subQuery = $this->selectBuilderFactory
                ->create()
                ->from($this->entityType)
                ->withStrictAccessControl()
                ->buildQueryBuilder()
                ->where([
                    'parentId' => $entity->getId(),
                ])
                ->build();

            $count = $this->getRepository()
                ->clone($subQuery)
                ->count();

            if (!$count) {
                $idList[] = $entity->getId();

                continue;
            }

            if ($includingRecords) {
                $isNotEmpty = false;

                $subCollection = $this->getRepository()
                    ->clone($subQuery)
                    ->find();

                foreach ($subCollection as $subEntity) {
                    if (!$this->checkItemIsEmpty($subEntity)) {
                        $isNotEmpty = true;

                        break;
                    }
                }

                if (!$isNotEmpty) {
                    $idList[] = $entity->getId();
                }
            }
        }

        return $idList;
    }

    private function getSubjectEntityType(): string
    {
        return $this->metadata->get("scopes.$this->entityType.categoryParentEntityType") ??
            $this->subjectEntityType ??
            substr($this->entityType, 0, strlen($this->entityType) - 8);
    }

    private function getCategoryField(): string
    {
        return $this->metadata->get("scopes.$this->entityType.categoryField") ??
            $this->categoryField ??
            'category';
    }
}
