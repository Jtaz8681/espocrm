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

namespace Espo\Core\Formula\Functions\ExtGroup\SmsGroup;

use Espo\Core\Formula\Exceptions\FunctionRuntimeError;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Sms\SmsSender;
use Espo\Entities\Sms;
use Espo\Core\Di;
use Exception;

/**
 * @noinspection PhpUnused
 */
class SendType extends BaseFunction implements

    Di\EntityManagerAware,
    Di\InjectableFactoryAware
{
    use Di\EntityManagerSetter;
    use Di\InjectableFactorySetter;

    public function process(ArgumentList $args)
    {
        if (count($args) < 1) {
            $this->throwTooFewArguments(1);
        }

        $evaluatedArgs = $this->evaluate($args);

        $id = $evaluatedArgs[0];

        if (!$id || !is_string($id)) {
            $this->throwBadArgumentType(1, 'string');
        }


        $sms = $this->entityManager->getRDBRepositoryByClass(Sms::class)->getById($id);

        if (!$sms) {
            throw new FunctionRuntimeError("SMS $id does not exist.");
        }

        if ($sms->getStatus() === Sms::STATUS_SENT) {
            throw new FunctionRuntimeError("Can't send SMS that has 'Sent' status.");
        }

        try {
            $this->createSender()->send($sms);

            $this->entityManager->saveEntity($sms);
        } catch (Exception $e) {
            $sms->setStatus(Sms::STATUS_FAILED);
            $this->entityManager->saveEntity($sms);

            throw new FunctionRuntimeError("Error while sending SMS. {$e->getMessage()}", previous: $e);
        }

        return true;
    }

    private function createSender(): SmsSender
    {
        return $this->injectableFactory->create(SmsSender::class);
    }
}
