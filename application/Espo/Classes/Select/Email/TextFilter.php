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

namespace Espo\Classes\Select\Email;

use Espo\Core\Select\Text\Filter;
use Espo\Core\Select\Text\Filter\Data;
use Espo\Core\Select\Text\DefaultFilter;
use Espo\Core\Select\Text\ConfigProvider;
use Espo\ORM\EntityManager;
use Espo\ORM\Name\Attribute;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\SelectBuilder;
use Espo\ORM\Query\SelectBuilder as QueryBuilder;
use Espo\ORM\Query\Part\Where\OrGroup;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\Entities\EmailAddress;

/**
 * @noinspection PhpUnused
 */
class TextFilter implements Filter
{
    public function __construct(
        private DefaultFilter $defaultFilter,
        private ConfigProvider $config,
        private EntityManager $entityManager
    ) {}

    public function apply(QueryBuilder $queryBuilder, Data $data): void
    {
        $filter = $data->getFilter();
        $ftWhereItem = $data->getFullTextSearchWhereItem();

        if (
            mb_strlen($filter) < $this->config->getMinLengthForContentSearch() ||
            !str_contains($filter, '@') ||
            $data->forceFullTextSearch()
        ) {
            $this->defaultFilter->apply($queryBuilder, $data);

            return;
        }

        $emailAddressId = $this->getEmailAddressIdByValue($filter);

        $orGroupBuilder = OrGroup::createBuilder();

        if ($ftWhereItem && !$emailAddressId) {
            $orGroupBuilder->add($ftWhereItem);
        }

        if (!$emailAddressId) {
            $orGroupBuilder->add(
                Cond::equal(Expr::column('id'), null)
            );

            $queryBuilder->where($orGroupBuilder->build());

            return;
        }

        $orGroupBuilder
            ->add(
                Cond::equal(
                    Expr::column('fromEmailAddressId'),
                    $emailAddressId
                )
            )
            ->add(
                Cond::exists(
                    SelectBuilder::create()
                        ->from('EmailEmailAddress', 'sq')
                        ->where(['emailAddressId' => $emailAddressId])
                        ->where(
                            Cond::equal(
                                Expr::column('sq.emailId'),
                                Expr::column('email.id')
                            )
                        )
                        ->build()
                )
            );

        $queryBuilder->where($orGroupBuilder->build());
    }

    private function getEmailAddressIdByValue(string $value): ?string
    {
        $emailAddress = $this->entityManager
            ->getRDBRepository(EmailAddress::ENTITY_TYPE)
            ->select(Attribute::ID)
            ->where(['lower' => strtolower($value)])
            ->findOne();

        if (!$emailAddress) {
            return null;
        }

        return $emailAddress->getId();
    }
}
