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
use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Utils\SystemUser;
use Espo\Entities\Email;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Di;
use Espo\Entities\EmailTemplate;
use Espo\Tools\EmailTemplate\Data;
use Espo\Tools\EmailTemplate\Params;
use Espo\Tools\EmailTemplate\Processor;

/**
 * @noinspection PhpUnused
 */
class ApplyTemplateType extends BaseFunction implements

    Di\EntityManagerAware,
    Di\InjectableFactoryAware
{
    use Di\EntityManagerSetter;
    use Di\InjectableFactorySetter;

    public function process(ArgumentList $args)
    {
        if (count($args) < 2) {
            $this->throwTooFewArguments(2);
        }

        $args = $this->evaluate($args);

        $id = $args[0];
        $templateId = $args[1];
        $parentType = $args[2] ?? null;
        $parentId = $args[3] ?? null;

        if (!$id || !is_string($id)) {
            $this->throwBadArgumentType(1, 'string');
        }

        if (!$templateId || !is_string($templateId)) {
            $this->throwBadArgumentType(2, 'string');
        }

        if ($parentType && !is_string($parentType)) {
            $this->throwBadArgumentType(3, 'string');
        }

        if ($parentId && !is_string($parentId)) {
            $this->throwBadArgumentType(4, 'string');
        }

        $em = $this->entityManager;

        $email = $em->getRDBRepositoryByClass(Email::class)->getById($id);
        $emailTemplate = $em->getRDBRepositoryByClass(EmailTemplate::class)->getById($templateId);

        if (!$email) {
            throw new FunctionRuntimeError("Email $id does not exist.");
        }

        if (!$emailTemplate) {
            throw new FunctionRuntimeError("EmailTemplate $templateId does not exist.");
        }

        $status = $email->getStatus();

        if ($status && $status === Email::STATUS_SENT) {
            throw new FunctionRuntimeError("Can't apply template to email with 'Sent' status.");
        }

        $processor = $this->injectableFactory->create(Processor::class);

        $params = Params::create()
            ->withCopyAttachments()
            ->withApplyAcl(false);

        $data = Data::create();

        if (!$parentType || !$parentId) {
            $parentType = $email->getParentType();
            $parentId = $email->getParentId();
        }

        if ($parentType && $parentId) {
            $data = $data
                ->withParentId($parentId)
                ->withParentType($parentType);
        }

        $data = $data->withEmailAddress(
            $email->getToAddressList()[0] ?? null
        );

        $emailData = $processor->process($emailTemplate, $params, $data);

        $attachmentsIdList = $email->getLinkMultipleIdList('attachments');

        $attachmentsIdList = array_merge(
            $attachmentsIdList,
            $emailData->getAttachmentIdList()
        );

        $email
            ->setSubject($emailData->getSubject())
            ->setBody($emailData->getBody())
            ->setIsHtml($emailData->isHtml())
            ->setAttachmentIdList($attachmentsIdList);

        $systemUserId = $this->injectableFactory->create(SystemUser::class)->getId();

        $em->saveEntity($email, [
            SaveOption::MODIFIED_BY_ID => $systemUserId,
        ]);

        return true;
    }
}
