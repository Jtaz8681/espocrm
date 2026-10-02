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

use Espo\Core\Utils\Client\ActionRenderer;
use Espo\Entities\EmailAddress;
use Espo\Modules\Crm\Entities\Campaign;
use Espo\Modules\Crm\Tools\Campaign\LogService;
use Espo\Repositories\EmailAddress as EmailAddressRepository;
use Espo\Modules\Crm\Entities\CampaignTrackingUrl;
use Espo\Modules\Crm\Entities\EmailQueueItem;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\EntryPoint\EntryPoint;
use Espo\Core\EntryPoint\Traits\NoAuth;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFoundSilent;
use Espo\Core\HookManager;
use Espo\Core\ORM\EntityManager;
use Espo\Core\Utils\Hasher;
use Espo\Core\Utils\Metadata;

class CampaignUrl implements EntryPoint
{
    use NoAuth;

    public function __construct(
        private EntityManager $entityManager,
        private LogService $service,
        private Hasher $hasher,
        private HookManager $hookManager,
        private Metadata $metadata,
        private ActionRenderer $actionRenderer
    ) {}

    /**
     * @throws BadRequest
     * @throws NotFoundSilent
     */
    public function run(Request $request, Response $response): void
    {
        $queueItemId = $request->getQueryParam('queueItemId') ?? null;
        $trackingUrlId = $request->getQueryParam('id') ?? null;
        $emailAddress = $request->getQueryParam('emailAddress') ?? null;
        $hash = $request->getQueryParam('hash') ?? null;
        $uid = $request->getQueryParam('uid') ?? null;

        if (!$trackingUrlId) {
            throw new BadRequest("No tracking URL ID.");
        }

        /** @var ?CampaignTrackingUrl $trackingUrl */
        $trackingUrl = $this->entityManager->getEntityById(CampaignTrackingUrl::ENTITY_TYPE, $trackingUrlId);

        if (!$trackingUrl) {
            throw new NotFoundSilent("Tracking URL '$trackingUrlId' not found.");
        }

        if ($emailAddress && $hash) {
            $this->processWithHash($trackingUrl, $emailAddress, $hash);
        } else if ($uid && $hash) {
            $this->processWithUniqueId($trackingUrl, $uid, $hash);
        } else {
            if (!$queueItemId) {
                throw new BadRequest("No item ID.");
            }

            /** @var ?EmailQueueItem $queueItem */
            $queueItem = $this->entityManager->getEntityById(EmailQueueItem::ENTITY_TYPE, $queueItemId);

            if (!$queueItem) {
                throw new NotFoundSilent("Item not found.");
            }

            $this->processWithQueueItem($trackingUrl, $queueItem);
        }

        if ($trackingUrl->getAction() === CampaignTrackingUrl::ACTION_SHOW_MESSAGE) {
            $this->displayMessage($response, $trackingUrl->getMessage());

            return;
        }

        $url = $trackingUrl->getUrl();

        if ($url) {
            ob_clean();

            header('Location: ' . $url);

            die;
        }
    }

    private function processWithQueueItem(CampaignTrackingUrl $trackingUrl, EmailQueueItem $queueItem): void
    {
        $campaign = null;

        $targetType = $queueItem->getTargetType();
        $targetId = $queueItem->getTargetId();

        $target = $this->entityManager->getEntityById($targetType, $targetId);

        $campaignId = $trackingUrl->getCampaignId();

        if ($campaignId) {
            $campaign = $this->entityManager->getEntityById(Campaign::ENTITY_TYPE, $campaignId);
        }

        if ($target) {
            $this->hookManager->process(CampaignTrackingUrl::ENTITY_TYPE, 'afterClick', $trackingUrl, [], [
                'targetId' => $targetId,
                'targetType' => $targetType,
            ]);
        }

        if ($campaign && $target) {
            $this->service->logClicked($campaign->getId(), $queueItem, $trackingUrl);
        }
    }

    /**
     * @throws NotFoundSilent
     */
    private function processWithHash(CampaignTrackingUrl $trackingUrl, string $emailAddress, string $hash): void
    {
        $hashActual = $this->hasher->hash($emailAddress);

        if ($hashActual !== $hash) {
            throw new NotFoundSilent();
        }

        $eaRepository = $this->getEmailAddressRepository();

        $ea = $eaRepository->getByAddress($emailAddress);

        if (!$ea) {
            throw new NotFoundSilent();
        }

        $entityList = $eaRepository->getEntityListByAddressId($ea->getId());

        foreach ($entityList as $target) {
            $this->hookManager->process(CampaignTrackingUrl::ENTITY_TYPE, 'afterClick', $trackingUrl, [], [
                'targetId' => $target->getId(),
                'targetType' => $target->getEntityType(),
            ]);
        }
    }

    /**
     * @throws NotFoundSilent
     */
    private function processWithUniqueId(CampaignTrackingUrl $trackingUrl, string $uid, string $hash): void
    {
        $hashActual = $this->hasher->hash($uid);

        if ($hashActual !== $hash) {
            throw new NotFoundSilent();
        }

        $this->hookManager->process(CampaignTrackingUrl::ENTITY_TYPE, 'afterClick', $trackingUrl, [], [
            'uid' => $uid,
        ]);
    }

    private function displayMessage(Response $response, ?string $message): void
    {
        $data = [
            'message' => $message ?? '',
            'view' => $this->metadata->get(['clientDefs', 'Campaign', 'trackingUrlMessageView']),
            'template' => $this->metadata->get(['clientDefs', 'Campaign', 'trackingUrlMessageTemplate']),
        ];

        $params = ActionRenderer\Params::create('crm:controllers/tracking-url', 'displayMessage', $data);

        $this->actionRenderer->write($response, $params);
    }

    private function getEmailAddressRepository(): EmailAddressRepository
    {
        /** @var EmailAddressRepository */
        return $this->entityManager->getRepository(EmailAddress::ENTITY_TYPE);
    }
}
