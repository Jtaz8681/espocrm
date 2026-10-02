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

namespace Espo\Repositories;

use Espo\Core\Name\Field;
use Espo\Entities\User as UserEntity;
use Espo\ORM\Entity;
use Espo\Entities\PhoneNumber as PhoneNumberEntity;
use Espo\Core\Repositories\Database;
use Espo\Core\Di;

use stdClass;

/**
 * Not to be used directly. Use utilities from `Espo\Tools\PhoneNumber` instead.
 * @internal
 * @extends Database<PhoneNumberEntity>
 */
class PhoneNumber extends Database implements

    Di\ApplicationStateAware,
    Di\AclManagerAware,
    Di\ConfigAware
{
    use Di\ApplicationStateSetter;
    use Di\AclManagerSetter;
    use Di\ConfigSetter;

    private const ERASED_PREFIX = 'ERASED:';

    private const LOOKUP_SMALL_MAX_SIZE = 20;
    private const LOOKUP_MAX_SIZE = 50;

    /**
     * @param string[] $numberList
     * @return string[]
     */
    public function getIds($numberList = []): array
    {
        if (empty($numberList)) {
            return [];
        }

        $ids = [];

        $phoneNumbers = $this
            ->where([
                'name' => $numberList,
            ])
            ->find();

        $exist = [];

        foreach ($phoneNumbers as $phoneNumber) {
            $ids[] = $phoneNumber->getId();
            $exist[] = $phoneNumber->get(Field::NAME);
        }

        foreach ($numberList as $number) {
            $number = trim($number);

            if (empty($number)) {
                continue;
            }

            if (!in_array($number, $exist)) {
                $phoneNumber = $this->getNew();
                $phoneNumber->set(Field::NAME, $number);
                $this->save($phoneNumber);

                $ids[] = $phoneNumber->getId();
            }
        }

        return $ids;
    }

    /**
     * @return array<int, stdClass>
     */
    public function getPhoneNumberData(Entity $entity): array
    {
        if (!$entity->hasId()) {
            return [];
        }

        $dataList = [];

        $numberList = $this
            ->select([Field::NAME, 'type', 'invalid', 'optOut', ['en.primary', 'primary']])
            ->join(
                PhoneNumberEntity::RELATION_ENTITY_PHONE_NUMBER,
                'en',
                [
                    'en.phoneNumberId:' => 'id',
                ]
            )
            ->where([
                'en.entityId' => $entity->getId(),
                'en.entityType' => $entity->getEntityType(),
                'en.deleted' => false,
            ])
            ->order('en.primary', true)
            ->find();

        foreach ($numberList as $number) {
            $item = (object) [
                'phoneNumber' => $number->get(Field::NAME),
                'type' => $number->get('type'),
                'primary' => $number->get('primary'),
                'optOut' => $number->get('optOut'),
                'invalid' => $number->get('invalid'),
            ];

            $dataList[] = $item;
        }

        return $dataList;
    }

    public function getByNumber(string $number): ?PhoneNumberEntity
    {
        /** @var ?PhoneNumberEntity */
        return $this->where(['name' => $number])->findOne();
    }

    /**
     * @return Entity[]
     */
    public function getEntityListByPhoneNumberId(string $phoneNumberId, ?Entity $exceptionEntity = null): array
    {
        $entityList = [];

        $where = [
            'phoneNumberId' => $phoneNumberId,
        ];

        if ($exceptionEntity) {
            $where[] = [
                'OR' => [
                    'entityType!=' => $exceptionEntity->getEntityType(),
                    'entityId!=' => $exceptionEntity->getId(),
                ]
            ];
        }

        $itemList = $this->entityManager
            ->getRDBRepository(PhoneNumberEntity::RELATION_ENTITY_PHONE_NUMBER)
            ->sth()
            ->select(['entityType', 'entityId'])
            ->where($where)
            ->limit(0, self::LOOKUP_MAX_SIZE)
            ->find();

        foreach ($itemList as $item) {
            $itemEntityType = $item->get('entityType');
            $itemEntityId = $item->get('entityId');

            if (!$itemEntityType || !$itemEntityId) {
                continue;
            }

            if (!$this->entityManager->hasRepository($itemEntityType)) {
                continue;
            }

            $entity = $this->entityManager->getEntityById($itemEntityType, $itemEntityId);

            if (!$entity) {
                continue;
            }

            $entityList[] = $entity;
        }

        return $entityList;
    }

    /**
     * @param string[] $order
     */
    public function getEntityByPhoneNumberId(
        string $phoneNumberId,
        ?string $entityType = null,
        ?array $order = null
    ): ?Entity {

        $order ??= $this->config->get('phoneNumberEntityLookupDefaultOrder') ?? [];

        $where = ['phoneNumberId' => $phoneNumberId];

        if ($entityType) {
            $where[] = ['entityType' => $entityType];
        }

        $collection = $this->entityManager
            ->getRDBRepository(PhoneNumberEntity::RELATION_ENTITY_PHONE_NUMBER)
            ->sth()
            ->select(['entityType', 'entityId'])
            ->where($where)
            ->limit(0, self::LOOKUP_SMALL_MAX_SIZE)
            ->order([
                ['LIST:entityType:' . implode(',', $order)],
                ['primary', 'DESC'],
            ])
            ->find();

        foreach ($collection as $item) {
            $itemEntityType = $item->get('entityType');
            $itemEntityId = $item->get('entityId');

            if (!$itemEntityType || !$itemEntityId) {
                continue;
            }

            if (!$this->entityManager->hasRepository($itemEntityType)) {
                continue;
            }

            $entity = $this->entityManager->getEntityById($itemEntityType, $itemEntityId);

            if ($entity) {
                if ($entity instanceof UserEntity) {
                    if (!$entity->isActive()) {
                        continue;
                    }
                }

                return $entity;
            }
        }

        return null;
    }

    protected function beforeSave(Entity $entity, array $options = [])
    {
        parent::beforeSave($entity, $options);

        if ($entity->has(Field::NAME)) {
            $number = $entity->get(Field::NAME);

            if (is_string($number) && !str_starts_with($number, self::ERASED_PREFIX)) {
                $numeric = preg_replace('/[^0-9]/', '', $number);
            } else {
                $numeric = null;
            }

            $entity->set('numeric', $numeric);
        }
    }

    public function markNumberOptedOut(string $number, bool $isOptedOut = true): void
    {
        $phoneNumber = $this->getByNumber($number);

        if (!$phoneNumber) {
            return;
        }

        $phoneNumber->set('optOut', $isOptedOut);

        $this->save($phoneNumber);
    }

    public function markNumberInvalid(string $number, bool $isInvalid = true): void
    {
        $phoneNumber = $this->getByNumber($number);

        if (!$phoneNumber) {
            return;
        }

        $phoneNumber->set('invalid', $isInvalid);

        $this->save($phoneNumber);
    }
}
