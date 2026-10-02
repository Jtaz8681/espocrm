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

namespace Espo\Core\Mail\Account\GroupAccount\Hooks;

use Espo\Core\Mail\Account\Hook\BeforeFetch as BeforeFetchInterface;
use Espo\Core\Mail\Account\Hook\BeforeFetchResult;
use Espo\Core\Mail\Account\Account;
use Espo\Core\Mail\Importer\AutoReplyDetector;
use Espo\Core\Mail\Message;
use Espo\Core\Mail\Account\GroupAccount\BouncedRecognizer;
use Espo\Core\Utils\Log;
use Espo\Entities\EmailAddress;
use Espo\ORM\EntityManager;
use Espo\Repositories\EmailAddress as EmailAddressRepository;
use Espo\Modules\Crm\Entities\EmailQueueItem;
use Espo\Modules\Crm\Tools\Campaign\LogService as CampaignService;

use Throwable;

class BeforeFetch implements BeforeFetchInterface
{
    public function __construct(
        private Log $log,
        private EntityManager $entityManager,
        private BouncedRecognizer $bouncedRecognizer,
        private CampaignService $campaignService,
        private AutoReplyDetector $autoReplyDetector,
    ) {}

    public function process(Account $account, Message $message): BeforeFetchResult
    {
        if ($this->bouncedRecognizer->isBounced($message)) {
            try {
                $toSkip = $this->processBounced($message);
            } catch (Throwable $e) {
                $logMessage  = 'InboundEmail ' . $account->getId() . ' ' .
                    'Process Bounced Message; ' . $e->getCode() . ' ' . $e->getMessage();

                $this->log->error($logMessage, ['exception' => $e]);

                return BeforeFetchResult::create()->withToSkip();
            }

            if ($toSkip) {
                return BeforeFetchResult::create()->withToSkip();
            }
        }

        return BeforeFetchResult::create()
            ->with('skipAutoReply', $this->checkMessageCannotBeAutoReplied($message))
            ->with('isAutoSubmitted', $this->checkMessageIsAutoSubmitted($message));
    }

    private function processBounced(Message $message): bool
    {
        $isHard = $this->bouncedRecognizer->isHard($message);
        $queueItemId = $this->bouncedRecognizer->extractQueueItemId($message);

        if (!$queueItemId) {
            return false;
        }

        $queueItem = $this->entityManager->getRDBRepositoryByClass(EmailQueueItem::class)->getById($queueItemId);

        if (!$queueItem) {
            return false;
        }

        $campaignId = $queueItem->getMassEmail()?->getCampaignId();
        $emailAddress = $queueItem->getEmailAddress();

        if (!$emailAddress) {
            return true;
        }

        /** @var EmailAddressRepository $emailAddressRepository */
        $emailAddressRepository = $this->entityManager->getRepository(EmailAddress::ENTITY_TYPE);

        if ($isHard) {
            $emailAddressEntity = $emailAddressRepository->getByAddress($emailAddress);

            if ($emailAddressEntity) {
                $emailAddressEntity->setInvalid(true);

                $this->entityManager->saveEntity($emailAddressEntity);
            }
        }

        $targetType = $queueItem->getTargetType();
        $targetId = $queueItem->getTargetId();

        $target = $this->entityManager->getEntityById($targetType, $targetId);

        if ($campaignId && $target) {
            $this->campaignService->logBounced($campaignId, $queueItem, $isHard);
        }

        return true;
    }

    private function checkMessageIsAutoReply(Message $message): bool
    {
        if ($this->checkMessageIsAutoSubmitted($message)) {
            return true;
        }

        return $this->autoReplyDetector->detect($message);
    }

    private function checkMessageCannotBeAutoReplied(Message $message): bool
    {
        if (
            $message->getHeader('X-Auto-Response-Suppress') === 'AutoReply' ||
            $message->getHeader('X-Auto-Response-Suppress') === 'All'
        ) {
            return true;
        }

        if ($this->checkMessageIsAutoSubmitted($message)) {
            return true;
        }

        if ($this->checkMessageIsAutoReply($message)) {
            return true;
        }

        return false;
    }

    private function checkMessageIsAutoSubmitted(Message $message): bool
    {
        if ($this->autoReplyDetector->detect($message)) {
            return true;
        }

        return $message->getHeader('Auto-Submitted') &&
            strtolower($message->getHeader('Auto-Submitted')) !== 'no';
    }
}
