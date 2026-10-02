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

namespace Espo\Core\Controllers;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Api\Request;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Exceptions\NotFoundSilent;
use Espo\Core\Select\SearchParams;
use Espo\Core\Utils\Json;

use stdClass;

class Record extends RecordBase
{
    /**
     * List related records.
     *
     * @throws BadRequest
     * @throws NotFound
     * @throws Forbidden
     * @noinspection PhpUnused
     */
    public function getActionListLinked(Request $request): stdClass
    {
        $id = $request->getRouteParam('id');
        $link = $request->getRouteParam('link');

        if (!$id) {
            throw new BadRequest("No ID.");
        }

        if (!$link) {
            throw new BadRequest("No link.");
        }

        $searchParams = $this->fetchSearchParamsFromRequest($request);

        $result = $this->getRecordService()->findLinked($id, $link, $searchParams);

        return $result->toApiOutput();
    }

    /**
     * Relate records.
     *
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function postActionCreateLink(Request $request): bool
    {
        $id = $request->getRouteParam('id');
        $link = $request->getRouteParam('link');

        $data = $request->getParsedBody();

        if (!$id || !$link) {
            throw new BadRequest();
        }

        if (!empty($data->massRelate)) {
            $searchParams = $this->fetchMassLinkSearchParamsFromRequest($request);

            $result = $this->getRecordService()->massLink($id, $link, $searchParams);

            if ($result->getAffectedCount() === 0) {
                return false;
            }

            return true;
        }

        $foreignIdList = [];

        if (isset($data->id)) {
            $foreignIdList[] = $data->id;
        }

        if (isset($data->ids) && is_array($data->ids)) {
            foreach ($data->ids as $foreignId) {
                $foreignIdList[] = $foreignId;
            }
        }

        $result = false;

        foreach ($foreignIdList as $foreignId) {
            $this->getRecordService()->link($id, $link, $foreignId);

            $result = true;
        }

        return $result;
    }

    /**
     * Un-relate records.
     *
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function deleteActionRemoveLink(Request $request): bool
    {
        $id = $request->getRouteParam('id');
        $link = $request->getRouteParam('link');

        $data = $request->getParsedBody();

        if (!$id || !$link) {
            throw new BadRequest();
        }

        $foreignIdList = [];

        if (isset($data->id)) {
            $foreignIdList[] = $data->id;
        }

        if (isset($data->ids) && is_array($data->ids)) {
            foreach ($data->ids as $foreignId) {
                $foreignIdList[] = $foreignId;
            }
        }

        $result = false;

        foreach ($foreignIdList as $foreignId) {
            $this->getRecordService()->unlink($id, $link, $foreignId);

            $result = true;
        }

        return $result;
    }

    /**
     * Follow a record.
     *
     * @throws BadRequest
     * @throws NotFoundSilent
     * @throws Forbidden
     * @noinspection PhpUnused
     */
    public function putActionFollow(Request $request): bool
    {
        $id = $request->getRouteParam('id');

        if (!$id) {
            throw new BadRequest("No ID.");
        }

        $this->getRecordService()->follow($id);

        return true;
    }

    /**
     * Unfollow a record.
     *
     * @throws NotFoundSilent
     * @throws BadRequest
     * @noinspection PhpUnused
     */
    public function deleteActionUnfollow(Request $request): bool
    {
        $id = $request->getRouteParam('id');

        if (!$id) {
            throw new BadRequest("No ID.");
        }

        $this->getRecordService()->unfollow($id);

        return true;
    }

    /**
     * @throws BadRequest
     */
    private function fetchMassLinkSearchParamsFromRequest(Request $request): SearchParams
    {
        $data = $request->getParsedBody();

        $where = $data->where ?? null;

        if ($where !== null) {
            $where = json_decode(Json::encode($where), true);
        }

        $params = json_decode(
            Json::encode(
                $data->searchParams ?? $data->selectData ?? (object) []
            ),
            true
        );

        if ($where !== null && !is_array($where)) {
            throw new BadRequest("Bad 'where.");
        }

        if ($where !== null) {
            $params['where'] = array_merge(
                $params['where'] ?? [],
                $where
            );
        }

        unset($params['select']);

        return SearchParams::fromRaw($params);
    }
}
