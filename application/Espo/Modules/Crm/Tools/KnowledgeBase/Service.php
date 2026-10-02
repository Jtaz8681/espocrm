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

namespace Espo\Modules\Crm\Tools\KnowledgeBase;

use Espo\Core\Acl;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\Core\Select\Where\Item as WhereItem;
use Espo\Entities\Attachment;
use Espo\Modules\Crm\Entities\KnowledgeBaseArticle;
use Espo\ORM\EntityManager;
use Espo\Repositories\Attachment as AttachmentRepository;
use Espo\Tools\Attachment\AccessChecker as AttachmentAccessChecker;
use Espo\Tools\Attachment\FieldData;

class Service
{
    public function __construct(
        private EntityManager $entityManager,
        private AttachmentAccessChecker $attachmentAccessChecker,
        private ServiceContainer $serviceContainer,
        private SelectBuilderFactory $selectBuilderFactory,
        private Acl $acl,
    ) {}

    /**
     * Copy article attachments for re-using (e.g. in an email).
     *
     * @return Attachment[]
     * @throws NotFound
     * @throws Forbidden
     */
    public function copyAttachments(string $id, FieldData $fieldData): array
    {
        /** @var ?KnowledgeBaseArticle $entity */
        $entity = $this->serviceContainer
            ->get(KnowledgeBaseArticle::ENTITY_TYPE)
            ->getEntity($id);

        if (!$entity) {
            throw new NotFound();
        }

        $this->attachmentAccessChecker->check($fieldData);

        $list = [];

        foreach ($entity->getAttachmentIdList() as $attachmentId) {
            $attachment = $this->copyAttachment($attachmentId, $fieldData);

            if ($attachment) {
                $list[] = $attachment;
            }
        }

        return $list;
    }

    private function copyAttachment(string $attachmentId, FieldData $fieldData): ?Attachment
    {
        /** @var ?Attachment $attachment */
        $attachment = $this->entityManager
            ->getRDBRepositoryByClass(Attachment::class)
            ->getById($attachmentId);

        if (!$attachment) {
            return null;
        }

        $copied = $this->getAttachmentRepository()->getCopiedAttachment($attachment);

        $copied->set('parentType', $fieldData->getParentType());
        $copied->set('relatedType', $fieldData->getRelatedType());
        $copied->setTargetField($fieldData->getField());
        $copied->setRole(Attachment::ROLE_ATTACHMENT);

        $this->getAttachmentRepository()->save($copied);

        return $copied;
    }

    private function getAttachmentRepository(): AttachmentRepository
    {
        /** @var AttachmentRepository */
        return $this->entityManager->getRepositoryByClass(Attachment::class);
    }

    /**
     * @throws NotFound
     * @throws Forbidden
     * @throws Error
     * @throws BadRequest
     */
    public function moveUp(string $id, SearchParams $params): void
    {
        /** @var ?KnowledgeBaseArticle $entity */
        $entity = $this->entityManager->getEntityById(KnowledgeBaseArticle::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden();
        }

        $currentIndex = $entity->getOrder();

        if (!is_int($currentIndex)) {
            throw new Error();
        }

        $query = $this->selectBuilderFactory
            ->create()
            ->from(KnowledgeBaseArticle::ENTITY_TYPE)
            ->withStrictAccessControl()
            ->withSearchParams($params)
            ->buildQueryBuilder()
            ->where([
                'order<' => $currentIndex,
            ])
            ->order([
                ['order', 'DESC'],
            ])
            ->limit()
            ->build();

        /** @var ?KnowledgeBaseArticle $previousEntity */
        $previousEntity = $this->entityManager
            ->getRDBRepositoryByClass(KnowledgeBaseArticle::class)
            ->clone($query)
            ->findOne();

        if (!$previousEntity) {
            return;
        }

        $entity->set('order', $previousEntity->getOrder());

        $previousEntity->set('order', $currentIndex);

        $this->entityManager->saveEntity($entity, [SaveOption::SILENT => true]);
        $this->entityManager->saveEntity($previousEntity, [SaveOption::SILENT => true]);
    }

    /**
     * @throws NotFound
     * @throws Forbidden
     * @throws Error
     * @throws BadRequest
     */
    public function moveDown(string $id, SearchParams $params): void
    {
        /** @var ?KnowledgeBaseArticle $entity */
        $entity = $this->entityManager->getEntityById(KnowledgeBaseArticle::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden();
        }

        $currentIndex = $entity->getOrder();

        if (!is_int($currentIndex)) {
            throw new Error();
        }

        $query = $this->selectBuilderFactory
            ->create()
            ->from(KnowledgeBaseArticle::ENTITY_TYPE)
            ->withStrictAccessControl()
            ->withSearchParams($params)
            ->buildQueryBuilder()
            ->where([
                'order>' => $currentIndex,
            ])
            ->order([
                ['order', 'ASC'],
            ])
            ->limit()
            ->build();

        /** @var ?KnowledgeBaseArticle $nextEntity */
        $nextEntity = $this->entityManager
            ->getRDBRepositoryByClass(KnowledgeBaseArticle::class)
            ->clone($query)
            ->findOne();

        if (!$nextEntity) {
            return;
        }

        $entity->set('order', $nextEntity->getOrder());

        $nextEntity->set('order', $currentIndex);

        $this->entityManager->saveEntity($entity, [SaveOption::SILENT => true]);
        $this->entityManager->saveEntity($nextEntity, [SaveOption::SILENT => true]);
    }

    /**
     * @throws NotFound
     * @throws Forbidden
     * @throws Error
     * @throws BadRequest
     */
    public function moveToTop(string $id, SearchParams $params): void
    {
        /** @var ?KnowledgeBaseArticle $entity */
        $entity = $this->entityManager->getEntityById(KnowledgeBaseArticle::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden();
        }

        $currentIndex = $entity->getOrder();

        if (!is_int($currentIndex)) {
            throw new Error();
        }

        $query = $this->selectBuilderFactory
            ->create()
            ->from(KnowledgeBaseArticle::ENTITY_TYPE)
            ->withStrictAccessControl()
            ->withSearchParams($params)
            ->buildQueryBuilder()
            ->where([
                'order<' => $currentIndex,
            ])
            ->order([
                ['order', 'ASC'],
            ])
            ->limit()
            ->build();

        /** @var ?KnowledgeBaseArticle $previousEntity */
        $previousEntity = $this->entityManager
            ->getRDBRepositoryByClass(KnowledgeBaseArticle::class)
            ->clone($query)
            ->findOne();

        if (!$previousEntity) {
            return;
        }

        $entity->set('order', $previousEntity->getOrder() - 1);

        $this->entityManager->saveEntity($entity, [SaveOption::SILENT => true]);
    }

    /**
     * @throws NotFound
     * @throws Forbidden
     * @throws Error
     * @throws BadRequest
     */
    public function moveToBottom(string $id, SearchParams $params): void
    {
        /** @var ?KnowledgeBaseArticle $entity */
        $entity = $this->entityManager->getEntityById(KnowledgeBaseArticle::ENTITY_TYPE, $id);

        if (!$entity) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($entity)) {
            throw new Forbidden();
        }

        $currentIndex = $entity->getOrder();

        if (!is_int($currentIndex)) {
            throw new Error();
        }

        $query = $this->selectBuilderFactory
            ->create()
            ->from(KnowledgeBaseArticle::ENTITY_TYPE)
            ->withStrictAccessControl()
            ->withSearchParams($params)
            ->buildQueryBuilder()
            ->where([
                'order>' => $currentIndex,
            ])
            ->order([
                ['order', 'DESC'],
            ])
            ->limit()
            ->build();

        /** @var ?KnowledgeBaseArticle $nextEntity */
        $nextEntity = $this->entityManager
            ->getRDBRepositoryByClass(KnowledgeBaseArticle::class)
            ->clone($query)
            ->findOne();

        if (!$nextEntity) {
            return;
        }

        $entity->set('order', $nextEntity->getOrder() + 1);

        $this->entityManager->saveEntity($entity, [SaveOption::SILENT => true]);
    }
}
