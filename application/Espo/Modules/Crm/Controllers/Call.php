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

use Espo\Core\Api\Response;
use Espo\Core\Controllers\Record;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Api\Request;
use Espo\Core\Mail\Exceptions\SendingError;
use Espo\Core\Utils\Json;
use Espo\Modules\Crm\Entities\Call as CallEntity;
use Espo\Modules\Crm\Tools\Meeting\InvitationService;
use Espo\Modules\Crm\Tools\Meeting\Invitation\Invitee;
use Espo\Modules\Crm\Tools\Meeting\Service;
use stdClass;

class Call extends Record
{
    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws SendingError
     * @throws NotFound
     */
    public function postActionSendInvitations(Request $request): stdClass
    {
        $id = $request->getParsedBody()->id ?? null;

        if (!$id) {
            throw new BadRequest();
        }

        $invitees = $this->fetchInvitees($request);

        $sentToList = $this->injectableFactory
            ->create(InvitationService::class)
            ->send(CallEntity::ENTITY_TYPE, $id, $invitees);

        return (object) [
            'idList' => array_map(fn ($it) => $it->getId() , $sentToList),
        ];
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws SendingError
     * @throws NotFound
     */
    public function postActionSendCancellation(Request $request): stdClass
    {
        $id = $request->getParsedBody()->id ?? null;

        if (!$id) {
            throw new BadRequest("No id.");
        }

        $invitees = $this->fetchInvitees($request);

        $sentToList = $this->injectableFactory
            ->create(InvitationService::class)
            ->sendCancellation(CallEntity::ENTITY_TYPE, $id, $invitees);

        return (object) [
            'idList' => array_map(fn ($it) => $it->getId() , $sentToList),
        ];
    }

    /**
     * @param Request $request
     * @return ?\Espo\Modules\Crm\Tools\Meeting\Invitation\Invitee[]
     * @throws BadRequest
     */
    private function fetchInvitees(Request $request): ?array
    {
        $targets = $request->getParsedBody()->targets ?? null;

        if ($targets === null) {
            return null;
        }

        if (!is_array($targets)) {
            throw new BadRequest("No targets.");
        }

        $invitees = [];

        foreach ($targets as $target) {
            if (!$target instanceof stdClass) {
                throw new BadRequest("Bad target.");
            }

            $targetEntityType = $target->entityType ?? null;
            $targetId = $target->id ?? null;

            if (!is_string($targetEntityType) || !is_string($targetId)) {
                throw new BadRequest("No entityType or id.");
            }

            $invitees[] = new Invitee($targetEntityType, $targetId);
        }

        return $invitees;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function postActionMassSetHeld(Request $request): bool
    {
        $ids = $request->getParsedBody()->ids ?? null;

        if (!is_array($ids)) {
            throw new BadRequest("No `ids`.");
        }

        $this->injectableFactory
            ->create(Service::class)
            ->massSetHeld(CallEntity::ENTITY_TYPE, $ids);

        return true;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     */
    public function postActionMassSetNotHeld(Request $request): bool
    {
        $ids = $request->getParsedBody()->ids ?? null;

        if (!is_array($ids)) {
            throw new BadRequest("No `ids`.");
        }

        $this->injectableFactory
            ->create(Service::class)
            ->massSetNotHeld(CallEntity::ENTITY_TYPE, $ids);

        return true;
    }

    /**
     * @throws BadRequest
     * @throws NotFound
     * @throws Forbidden
     */
    public function postActionSetAcceptanceStatus(Request $request): bool
    {
        $data = $request->getParsedBody();

        if (empty($data->id) || empty($data->status)) {
            throw new BadRequest("No id or status.");
        }

        $this->injectableFactory
            ->create(Service::class)
            ->setAcceptance(CallEntity::ENTITY_TYPE, $data->id, $data->status);

        return true;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function getActionAttendees(Request $request, Response $response): void
    {
        $id = $request->getRouteParam('id');

        if (!$id) {
            throw new BadRequest("No id.");
        }

        $collection = $this->injectableFactory
            ->create(Service::class)
            ->getAttendees(CallEntity::ENTITY_TYPE, $id);

        $response->writeBody(
            Json::encode(['list' => $collection->getValueMapList()])
        );
    }
}
