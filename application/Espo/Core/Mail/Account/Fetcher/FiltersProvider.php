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

namespace Espo\Core\Mail\Account\Fetcher;

use Espo\Core\Mail\Account\Account;
use Espo\Entities\EmailFilter;
use Espo\Entities\InboundEmail;
use Espo\ORM\Collection;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\Part\Order;

class FiltersProvider
{
    public function __construct(
        private EntityManager $entityManager,
    ) {}

    /**
     * @return Collection<EmailFilter>
     */
    public function get(Account $account): Collection
    {
        $actionList = [EmailFilter::ACTION_SKIP];

        if ($account->getEntityType() === InboundEmail::ENTITY_TYPE) {
            $actionList[] = EmailFilter::ACTION_MOVE_TO_GROUP_FOLDER;
        }

        $builder = $this->entityManager
            ->getRDBRepository(EmailFilter::ENTITY_TYPE)
            ->where([
                'action' => $actionList,
                'OR' => [
                    [
                        'parentType' => $account->getEntityType(),
                        'parentId' => $account->getId(),
                        'action' => $actionList,
                    ],
                    [
                        'parentId' => null,
                        'action' => EmailFilter::ACTION_SKIP,
                    ],
                ]
            ]);

        if (count($actionList) > 1) {
            $builder->order(
                Order::createByPositionInList(
                    Expression::column('action'),
                    $actionList
                )
            );
        }

        /** @var Collection<EmailFilter> */
        return $builder->find();
    }
}
