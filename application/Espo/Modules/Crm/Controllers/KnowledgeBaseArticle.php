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

namespace Espo\Modules\Crm\Controllers;

use Espo\Core\Controllers\Record;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Api\Request;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Select\SearchParams;
use Espo\Core\Utils\Json;
use Espo\Modules\Crm\Tools\KnowledgeBase\Service as KBService;

use Espo\Tools\Attachment\FieldData;
use stdClass;

class KnowledgeBaseArticle extends Record
{
    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function postActionGetCopiedAttachments(Request $request): stdClass
    {
        $data = $request->getParsedBody();

        $id = $data->id ?? null;
        $field = $data->field ?? null;
        $parentType = $data->parentType ?? null;
        $relatedType = $data->relatedType ?? null;

        if (!$id || !$field) {
            throw new BadRequest("No `id` or `field`.");
        }

        try {
            $fieldData = new FieldData(
                $field,
                $parentType,
                $relatedType
            );
        } catch (Error $e) {
            throw new BadRequest($e->getMessage());
        }

        $list = $this->getArticleService()->copyAttachments($id, $fieldData);

        $ids = array_map(
            fn ($item) => $item->getId(),
            $list
        );

        $names = (object) [];

        foreach ($list as $item) {
            $names->{$item->getId()} = $item->getName();
        }

        return (object) [
            'ids' => $ids,
            'names' => $names,
        ];
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function postActionMoveToTop(Request $request): bool
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest();
        }

        $searchParams = $this->fetchSearchParams($request);

        $this->getArticleService()->moveToTop($data->id, $searchParams);

        return true;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function postActionMoveUp(Request $request): bool
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest();
        }

        $searchParams = $this->fetchSearchParams($request);

        $this->getArticleService()->moveUp($data->id, $searchParams);

        return true;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function postActionMoveDown(Request $request): bool
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest();
        }

        $searchParams = $this->fetchSearchParams($request);

        $this->getArticleService()->moveDown($data->id, $searchParams);

        return true;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function postActionMoveToBottom(Request $request): bool
    {
        $data = $request->getParsedBody();

        if (empty($data->id)) {
            throw new BadRequest();
        }

        $searchParams = $this->fetchSearchParams($request);

        $this->getArticleService()->moveToBottom($data->id, $searchParams);

        return true;
    }

    private function getArticleService(): KBService
    {
        return $this->injectableFactory->create(KBService::class);
    }

    private function fetchSearchParams(Request $request): SearchParams
    {
        $body = $request->getParsedBody();

        $searchParams = SearchParams::create();

        if ($body->whereGroup ?? null) {
            $rawWhere = Json::decode(Json::encode($body->whereGroup), true);

            $searchParams = SearchParams::fromRaw(['where' => $rawWhere]);
        }

        return $searchParams;
    }
}
