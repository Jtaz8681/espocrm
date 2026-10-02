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

namespace Espo\Controllers;

use Espo\Core\Exceptions\BadRequest;

use Espo\Core\Api\Request;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Field\DateTime;
use Espo\Core\Name\Field;
use Espo\Core\Record\SearchParamsFetcher;

use Espo\Core\Select\SearchParams;
use Espo\Core\Select\Where\Item as WhereItem;
use Espo\Entities\User as UserEntity;
use Espo\Tools\Stream\RecordService;

use Espo\Tools\Stream\UserRecordService;
use Espo\Tools\UserReaction\ReactionStreamService;
use stdClass;

class Stream
{
    public static string $defaultAction = 'list';

    public function __construct(
        private RecordService $service,
        private UserRecordService $userRecordService,
        private SearchParamsFetcher $searchParamsFetcher,
        private ReactionStreamService $reactionStreamService,
    ) {}

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function getActionList(Request $request): stdClass
    {
        $id = $request->getRouteParam('id');
        $scope = $request->getRouteParam('scope');

        if ($scope === null) {
            throw new BadRequest();
        }

        $searchParams = $this->fetchSearchParams($request);

        if ($scope === UserEntity::ENTITY_TYPE) {
            $collection = $this->userRecordService->find($id, $searchParams);

            $reactionsCheckDate = DateTime::createNow();

            $output = $collection->toApiOutput();

            $output->reactionsCheckDate = $reactionsCheckDate->toString();
            $output->updatedReactions = $this->getReactionUpdates($request, $id);

            return $output;
        }

        if ($id === null) {
            throw new BadRequest();
        }

        $collection = $this->service->find($scope, $id, $searchParams);
        $pinnedCollection = $this->service->getPinned($scope, $id);

        $output = $collection->toApiOutput();

        $output->pinnedList = $pinnedCollection->getValueMapList();

        return $output;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function getActionListPosts(Request $request): stdClass
    {
        $id = $request->getRouteParam('id');
        $scope = $request->getRouteParam('scope');

        if ($scope === null) {
            throw new BadRequest();
        }

        if ($id === null && $scope !== UserEntity::ENTITY_TYPE) {
            throw new BadRequest("No ID.");
        }

        $searchParams = $this->fetchSearchParams($request)
            ->withPrimaryFilter('posts');

        $result = $scope === UserEntity::ENTITY_TYPE ?
            $this->userRecordService->find($id, $searchParams) :
            $this->service->find($scope, $id ?? '', $searchParams);

        return $result->toApiOutput();
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function getActionListUpdates(Request $request): stdClass
    {
        $id = $request->getRouteParam('id');
        $scope = $request->getRouteParam('scope');

        if ($scope === null || $id === null) {
            throw new BadRequest();
        }

        $searchParams = $this->fetchSearchParams($request);

        $result = $this->service->findUpdates($scope, $id, $searchParams);

        return $result->toApiOutput();
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    private function fetchSearchParams(Request $request): SearchParams
    {
        $searchParams = $this->searchParamsFetcher->fetch($request);

        $after = $request->getQueryParam('after');
        $filter = $request->getQueryParam('filter');

        if ($after) {
            $searchParams = $searchParams
                ->withWhereAdded(
                    WhereItem
                        ::createBuilder()
                        ->setAttribute(Field::CREATED_AT)
                        ->setType(WhereItem\Type::AFTER)
                        ->setValue($after)
                        ->build()
                );
        }

        if ($filter) {
            $searchParams = $searchParams->withPrimaryFilter($filter);
        }

        if ($request->getQueryParam('skipOwn') === 'true') {
            $searchParams = $searchParams->withBoolFilterAdded('skipOwn');
        }

        $beforeNumber = $request->getQueryParam('beforeNumber');

        if ($beforeNumber) {
            $searchParams = $searchParams
                ->withWhereAdded(
                    WhereItem
                        ::createBuilder()
                        ->setAttribute('number')
                        ->setType(WhereItem\Type::LESS_THAN)
                        ->setValue($beforeNumber)
                        ->build()
                );
        }

        return $searchParams;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     * @return stdClass[]
     */
    private function getReactionUpdates(Request $request, ?string $id): array
    {
        $reactionsAfter = $request->getQueryParam('reactionsAfter');
        $noteIds = explode(',', $request->getQueryParam('reactionsCheckNoteIds') ?? '');

        if (!$reactionsAfter || !$noteIds) {
            return [];
        }

        return $this->reactionStreamService->getReactionUpdates(DateTime::fromString($reactionsAfter), $noteIds, $id);
    }
}
