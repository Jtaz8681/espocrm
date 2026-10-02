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

namespace Espo\Core\Formula\Functions\ExtGroup\EmailGroup;

use Espo\Core\Formula\Exceptions\FunctionRuntimeError;
use Espo\Core\Mail\ConfigDataProvider;
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Utils\SystemUser;
use Espo\Entities\Email;
use Espo\Tools\Email\SendService;
use Espo\Core\Di;
use Exception;

/**
 * @noinspection PhpUnused
 */
class SendType extends BaseFunction implements
    Di\EntityManagerAware,
    Di\ConfigAware,
    Di\InjectableFactoryAware,
    Di\RecordServiceContainerAware
{
    use Di\EntityManagerSetter;
    use Di\ConfigSetter;
    use Di\InjectableFactorySetter;
    use Di\RecordServiceContainerSetter;

    public function process(ArgumentList $args)
    {
        if (count($args) < 1) {
            $this->throwTooFewArguments(1);
        }

        $args = $this->evaluate($args);

        $id = $args[0];

        if (!$id || !is_string($id)) {
            $this->throwBadArgumentType(1, 'string');
        }

        $em = $this->entityManager;

        $email = $em->getRDBRepositoryByClass(Email::class)->getById($id);

        if (!$email) {
            throw new FunctionRuntimeError("Email $id does not exist.");
        }

        if ($email->getStatus() === Email::STATUS_SENT) {
            throw new FunctionRuntimeError("Can't send email that has 'Sent' status.");
        }

        $this->recordServiceContainer
            ->getByClass(Email::class)
            ->loadAdditionalFields($email);

        $toSave = false;

        if ($email->getStatus() !== Email::STATUS_SENDING) {
            $email->setStatus(Email::STATUS_SENDING);

            $toSave = true;
        }

        if (!$email->getFromAddress()) {
            $from = $this->injectableFactory
                ->create(ConfigDataProvider::class)
                ->getSystemOutboundAddress();

            if ($from) {
                $email->setFromAddress($from);

                $toSave = true;
            }
        }

        $systemUserId = $this->injectableFactory->create(SystemUser::class)->getId();

        if ($toSave) {
            $em->saveEntity($email, [
                SaveOption::SILENT => true,
                SaveOption::MODIFIED_BY_ID => $systemUserId,
            ]);
        }

        $sendService = $this->injectableFactory->create(SendService::class);

        try {
            $sendService->send($email);
        } catch (Exception $e) {
            $email->setStatus(Email::STATUS_FAILED);
            $em->saveEntity($email);

            throw new FunctionRuntimeError("Error while sending email. {$e->getMessage()}", previous: $e);
        }

        return true;
    }
}
