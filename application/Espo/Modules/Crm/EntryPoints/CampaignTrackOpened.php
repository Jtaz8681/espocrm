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

namespace Espo\Modules\Crm\EntryPoints;

use Espo\Entities\User;
use Espo\Modules\Crm\Entities\Campaign;
use Espo\Modules\Crm\Entities\EmailQueueItem;
use Espo\Modules\Crm\Entities\MassEmail;
use Espo\Modules\Crm\Tools\Campaign\LogService;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\EntryPoint\EntryPoint;
use Espo\Core\EntryPoint\Traits\NoAuth;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\ORM\EntityManager;

class CampaignTrackOpened implements EntryPoint
{
    use NoAuth;

    public function __construct(
        private EntityManager $entityManager,
        private LogService $service,
        private User $user,
    ) {}

    /**
     * @throws BadRequest
     * @throws NotFound
     */
    public function run(Request $request, Response $response): void
    {
        $id = $request->getQueryParam('id');

        if (!$id) {
            throw new BadRequest("No id.");
        }

        $queueItemId = $id;

        /** @var ?EmailQueueItem $queueItem */
        $queueItem = $this->entityManager->getEntityById(EmailQueueItem::ENTITY_TYPE, $queueItemId);

        if (!$queueItem) {
            throw new NotFound("Item not found.");
        }

        $targetType = $queueItem->getTargetType();
        $targetId = $queueItem->getTargetId();

        $target = $this->entityManager->getEntityById($targetType, $targetId);

        if (!$target) {
            return;
        }

        $massEmail = $queueItem->getMassEmail();

        if (!$massEmail) {
            return;
        }

        $campaign = $massEmail->getCampaign();

        if (!$campaign) {
            return;
        }

        if ($this->user->isSystem()) {
            $this->service->logOpened($campaign->getId(), $queueItem);
        }

        header('Content-Type: image/png');

        $img  = imagecreatetruecolor(1, 1);

        if (!$img) {
            return;
        }

        imagesavealpha($img, true);

        $color = imagecolorallocatealpha($img, 127, 127, 127, 127);

        if ($color === false) {
            return;
        }

        imagefill($img, 0, 0, $color);

        imagepng($img);
        imagecolordeallocate($img, $color);
        imagedestroy($img);
    }
}
