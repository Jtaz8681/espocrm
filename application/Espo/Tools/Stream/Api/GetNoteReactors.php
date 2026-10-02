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

namespace Espo\Tools\Stream\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Record\EntityProvider;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\Entities\Note;
use Espo\Entities\User;
use Espo\Entities\UserReaction;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Condition;
use Espo\ORM\Query\Part\Expression;
use Espo\ORM\Query\SelectBuilder;

/**
 * @noinspection PhpUnused
 */
class GetNoteReactors implements Action
{
    public function __construct(
        private EntityProvider $entityProvider,
        private SearchParamsFetcher $searchParamsFetcher,
        private SelectBuilderFactory $selectBuilderFactory,
        private EntityManager $entityManager,
    ) {}

    public function process(Request $request): Response
    {
        $id = $request->getRouteParam('id') ?? throw new BadRequest();
        $type = $request->getRouteParam('type') ?? throw new BadRequest();

        $note = $this->entityProvider->getByClass(Note::class, $id);
        $searchParams = $this->searchParamsFetcher->fetch($request);

        $query = $this->selectBuilderFactory
            ->create()
            ->from(User::ENTITY_TYPE)
            ->withSearchParams($searchParams)
            ->withStrictAccessControl()
            ->withDefaultOrder()
            ->buildQueryBuilder()
            ->select([
                'id',
                'name',
                'userName',
            ])
            ->where(
                Condition::in(
                    Expression::column('id'),
                    SelectBuilder::create()
                        ->from(UserReaction::ENTITY_TYPE)
                        ->select('userId')
                        ->where([
                            'type' => $type,
                            'parentId' => $note->getId(),
                            'parentType' => $note->getEntityType(),
                        ])
                        ->build()
                )
            )
            ->build();

        $repository = $this->entityManager->getRDBRepositoryByClass(User::class);

        $users = $repository->clone($query)->find();
        $count = $repository->clone($query)->count();

        return ResponseComposer::json([
            'list' => $users->getValueMapList(),
            'total' => $count,
        ]);
    }
}
