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

namespace Espo\Core\Sms;

use Espo\Core\InjectableFactory;
use Espo\Entities\Sms as SmsEntity;
use Espo\Core\Utils\Config;

class SmsSender
{
    private ?Sender $sender = null;

    public function __construct(
        private InjectableFactory $injectableFactory,
        private Config $config
    ) {}

    private function getSender(): Sender
    {
        if ($this->sender === null) {
            // Sender factory can throw an exception (if no 'smsProvider' in config).
            // Better it be thrown when sending rather than when instantiating
            // constructor dependencies.
            $this->sender = $this->injectableFactory->createResolved(Sender::class);
        }

        return $this->sender;
    }

    public function send(SmsEntity $sms): void
    {
        $systemFromNumber = $this->config->get('outboundSmsFromNumber');

        if ($sms->getFromNumber() === null && $systemFromNumber) {
            $sms->setFromNumber($systemFromNumber);
        }

        $this->getSender()->send($sms);

        $sms->setAsSent();
    }
}
