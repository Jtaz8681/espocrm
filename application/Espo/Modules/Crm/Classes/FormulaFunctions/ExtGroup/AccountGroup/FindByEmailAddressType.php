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

namespace Espo\Modules\Crm\Classes\FormulaFunctions\ExtGroup\AccountGroup;

use Espo\Core\Utils\Json;
use Espo\Modules\Crm\Entities\Account;
use Espo\Modules\Crm\Entities\Contact;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;

use Espo\Core\Di;

class FindByEmailAddressType extends BaseFunction implements
    Di\EntityManagerAware,
    Di\FileManagerAware
{
    use Di\EntityManagerSetter;
    use Di\FileManagerSetter;

    /** @var string[]  */
    private array $domainFileList = [
        'application/Espo/Modules/Crm/Resources/data/freeEmailProviderDomains.json',
        'custom/Espo/Custom/Resources/data/freeEmailProviderDomains.json',
    ];

    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);

        if (count($args) < 1) {
            $this->throwTooFewArguments(1);
        }

        $emailAddress = $args[0];

        if (!$emailAddress) {
            return null;
        }

        if (!is_string($emailAddress)) {
            $this->log("Formula: ext\\account\\findByEmailAddress: Bad argument type.");

            return null;
        }

        $domain = $emailAddress;

        if (str_contains($emailAddress, '@')) {
            [, $domain] = explode('@', $emailAddress);
        }

        $domain = strtolower($domain);

        $em = $this->entityManager;

        $account = $em->getRDBRepository(Account::ENTITY_TYPE)
            ->where(['emailAddress' => $emailAddress])
            ->findOne();

        if ($account) {
            return $account->getId();
        }

        $ignoreList = [];

        foreach ($this->domainFileList as $file) {
            if (!$this->fileManager->isFile($file)) {
                continue;
            }

            $ignoreList = array_merge(
                $ignoreList,
                Json::decode($this->fileManager->getContents($file))
            );
        }

        $contact = $em->getRDBRepository(Contact::ENTITY_TYPE)
            ->where(['emailAddress' => $emailAddress])
            ->findOne();

        if ($contact) {
            if (!in_array($domain, $ignoreList)) {
                $account = $em->getRDBRepository(Account::ENTITY_TYPE)
                    ->join('contacts')
                    ->where([
                        'emailAddress*' => '%@' . $domain,
                        'contacts.id' => $contact->getId(),
                    ])
                    ->findOne();

                if ($account) {
                    return $account->getId();
                }
            } else {
                if ($contact->get('accountId')) {
                    return $contact->get('accountId');
                }
            }
        }

        if (in_array($domain, $ignoreList)) {
            return null;
        }

        $account = $em->getRDBRepository(Account::ENTITY_TYPE)
            ->where(['emailAddress*' => '%@' . $domain])
            ->findOne();

        if (!$account) {
            return null;
        }

        return $account->getId();
    }
}
