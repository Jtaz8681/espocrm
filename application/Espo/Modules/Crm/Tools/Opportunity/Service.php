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

namespace Espo\Modules\Crm\Tools\Opportunity;

use Espo\Core\Acl;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Field\EmailAddress;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Modules\Crm\Entities\Opportunity;
use Espo\ORM\Collection;
use Espo\ORM\EntityManager;
use Espo\Tools\Email\EmailAddressEntityPair;
use RuntimeException;

class Service
{

    public function __construct(
        private ServiceContainer $serviceContainer,
        private Acl $acl,
        private EntityManager $entityManager,
        private SelectBuilderFactory $selectBuilderFactory
    ) {}

    /**
     * @return EmailAddressEntityPair[]
     * @throws Forbidden
     */
    public function getEmailAddressList(string $id): array
    {
        /** @var Opportunity $entity */
        $entity = $this->serviceContainer
            ->get(Opportunity::ENTITY_TYPE)
            ->getEntity($id);

        $list = [];

        if (
            $this->acl->checkField(Opportunity::ENTITY_TYPE, 'contacts') &&
            $this->acl->checkScope(Contact::ENTITY_TYPE)
        ) {
            foreach ($this->getContactEmailAddressList($entity) as $item) {
                $list[] = $item;
            }
        }

        if (
            $list === [] &&
            $this->acl->checkField(Opportunity::ENTITY_TYPE, 'account') &&
            $this->acl->checkScope(Account::ENTITY_TYPE)
        ) {
            $item = $this->getAccountEmailAddress($entity, $list);

            if ($item) {
                $list[] = $item;
            }
        }

        return $list;
    }

    /**
     * @param EmailAddressEntityPair[] $dataList
     */
    private function getAccountEmailAddress(Opportunity $entity, array $dataList): ?EmailAddressEntityPair
    {
        $accountLink = $entity->getAccount();

        if (!$accountLink) {
            return null;
        }

        /** @var ?Account $account */
        $account = $this->entityManager->getEntityById(Account::ENTITY_TYPE, $accountLink->getId());

        if (!$account) {
            return null;
        }

        $emailAddress = $account->getEmailAddress();

        if (!$emailAddress) {
            return null;
        }

        if (!$this->acl->checkEntity($account)) {
            return null;
        }

        foreach ($dataList as $item) {
            if ($item->getEmailAddress()->getAddress() === $emailAddress) {
                return null;
            }
        }

        return new EmailAddressEntityPair(EmailAddress::create($emailAddress), $account);
    }

    /**
     * @return EmailAddressEntityPair[]
     */
    private function getContactEmailAddressList(Opportunity $entity): array
    {
        $contactsLinkMultiple = $entity->getContacts();

        $contactIdList = $contactsLinkMultiple->getIdList();

        if (!count($contactIdList)) {
            return [];
        }

        if (!$this->acl->checkField(Contact::ENTITY_TYPE, 'emailAddress')) {
            return [];
        }

        $dataList = [];

        $emailAddressList = [];

        try {
            $query = $this->selectBuilderFactory
                ->create()
                ->from(Contact::ENTITY_TYPE)
                ->withStrictAccessControl()
                ->buildQueryBuilder()
                ->select([
                    'id',
                    'emailAddress',
                    'name',
                ])
                ->where([
                    'id' => $contactIdList,
                ])
                ->build();
        } catch (BadRequest|Forbidden $e) {
            throw new RuntimeException($e->getMessage());
        }

        /** @var Collection<Contact> $contactCollection */
        $contactCollection = $this->entityManager
            ->getRDBRepositoryByClass(Contact::class)
            ->clone($query)
            ->find();

        foreach ($contactCollection as $contact) {
            $emailAddress = $contact->getEmailAddress();

            if (!$emailAddress) {
                continue;
            }

            if (in_array($emailAddress, $emailAddressList)) {
                continue;
            }

            $emailAddressList[] = $emailAddress;

            $dataList[] = new EmailAddressEntityPair(EmailAddress::create($emailAddress), $contact);
        }

        $contact = $entity->getContact();

        if (!$contact) {
            return $dataList;
        }

        usort(
            $dataList,
            function (
                EmailAddressEntityPair $o1,
                EmailAddressEntityPair $o2
            ) use ($contact) {
                if ($o1->getEntity()->getId() === $contact->getId()) {
                    return -1;
                }

                if ($o2->getEntity()->getId() === $contact->getId()) {
                    return 1;
                }

                return 0;
            }
        );

        return $dataList;
    }
}
