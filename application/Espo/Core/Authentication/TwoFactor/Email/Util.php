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

namespace Espo\Core\Authentication\TwoFactor\Email;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Mail\Exceptions\SendingError;
use Espo\Core\Name\Field;
use Espo\Core\Utils\Config;
use Espo\Core\Mail\EmailSender;
use Espo\Core\Mail\EmailFactory;
use Espo\Core\Utils\TemplateFileManager;
use Espo\Core\Htmlizer\HtmlizerFactory;
use Espo\Core\Field\DateTime;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\Entities\User;
use Espo\Entities\Email;
use Espo\Entities\TwoFactorCode;
use Espo\Entities\UserData;
use Espo\Repositories\UserData as UserDataRepository;

use RuntimeException;

use const STR_PAD_LEFT;

class Util
{
    /**
     * A lifetime of a code.
     */
    private const CODE_LIFETIME_PERIOD = '10 minutes';

    /**
     * A max number of attempts to try a single code.
     */
    private const CODE_ATTEMPTS_COUNT = 5;

    /**
     * A length of a code.
     */
    private const CODE_LENGTH = 7;

    /**
     * A max number of codes tried by a user in a period defined by `CODE_LIMIT_PERIOD`.
     */
    private const CODE_LIMIT = 5;

    /**
     * A period for limiting trying to too many codes.
     */
    private const CODE_LIMIT_PERIOD = '10 minutes';

    public function __construct(
        private EntityManager $entityManager,
        private Config $config,
        private EmailSender $emailSender,
        private TemplateFileManager $templateFileManager,
        private HtmlizerFactory $htmlizerFactory,
        private EmailFactory $emailFactory
    ) {}

    /**
     * @throws Forbidden
     */
    public function storeEmailAddress(User $user, string $emailAddress): void
    {
        $this->checkEmailAddressIsUsers($user, $emailAddress);

        $userData = $this->getUserDataRepository()->getByUserId($user->getId());

        if (!$userData) {
            throw new RuntimeException("UserData not found.");
        }

        $userData->set('auth2FAEmailAddress', $emailAddress);

        $this->entityManager->saveEntity($userData);
    }

    public function verifyCode(User $user, string $code): bool
    {
        $codeEntity = $this->findCodeEntity($user);

        if (!$codeEntity) {
            return false;
        }

        if ($codeEntity->getAttemptsLeft() <= 1) {
            $this->decrementAttemptsLeft($codeEntity);
            $this->inactivateExistingCodeRecords($user);

            return false;
        }

        if ($codeEntity->getCode() !== $code) {
            $this->decrementAttemptsLeft($codeEntity);

            return false;
        }

        if (!$this->isCodeValidByLifetime($codeEntity)) {
            $this->inactivateExistingCodeRecords($user);

            return false;
        }

        $this->inactivateExistingCodeRecords($user);

        return true;
    }

    /**
     * @throws SendingError
     * @throws Forbidden
     */
    public function sendCode(User $user, ?string $emailAddress = null): void
    {
        if ($emailAddress === null) {
            $emailAddress = $this->getEmailAddress($user);
        }

        $this->checkEmailAddressIsUsers($user, $emailAddress);
        $this->checkCodeLimit($user);

        $code = $this->generateCode();

        $this->inactivateExistingCodeRecords($user);
        $this->createCodeRecord($user, $code);

        $email = $this->createEmail($user, $code, $emailAddress);

        $this->emailSender->send($email);
    }

    private function isCodeValidByLifetime(TwoFactorCode $codeEntity): bool
    {
        $period = $this->config->get('auth2FAEmailCodeLifetimePeriod') ?? self::CODE_LIFETIME_PERIOD;

        $validUntil = $codeEntity->getCreatedAt()->modify($period);

        if (DateTime::createNow()->diff($validUntil)->invert) {
            return false;
        }

        return true;
    }

    private function findCodeEntity(User $user): ?TwoFactorCode
    {
        /** @var ?TwoFactorCode */
        return $this->entityManager
            ->getRDBRepository(TwoFactorCode::ENTITY_TYPE)
            ->where([
                'method' => EmailLogin::NAME,
                'userId' => $user->getId(),
                'isActive' => true,
            ])
            ->findOne();
    }

    /**
     * @throws Forbidden
     */
    private function getEmailAddress(User $user): string
    {
        $userData = $this->getUserDataRepository()->getByUserId($user->getId());

        if (!$userData) {
            throw new RuntimeException("UserData not found.");
        }

        $emailAddress = $userData->get('auth2FAEmailAddress');

        if ($emailAddress) {
            return $emailAddress;
        }

        if ($user->getEmailAddressGroup()->getCount() === 0) {
            throw new Forbidden("User does not have email address.");
        }

        /** @var string */
        return $user->getEmailAddressGroup()->getPrimaryAddress();
    }

    /**
     * @throws Forbidden
     */
    private function checkEmailAddressIsUsers(User $user, string $emailAddress): void
    {
        $userAddressList = array_map(
            function (string $item) {
                return strtolower($item);
            },
            $user->getEmailAddressGroup()->getAddressList()
        );

        if (!in_array(strtolower($emailAddress), $userAddressList)) {
            throw new Forbidden("Email address is not one of user's.");
        }
    }

    /**
     * @throws Forbidden
     */
    private function checkCodeLimit(User $user): void
    {
        $limit = $this->config->get('auth2FAEmailCodeLimit') ?? self::CODE_LIMIT;
        $period = $this->config->get('auth2FAEmailCodeLimitPeriod') ?? self::CODE_LIMIT_PERIOD;

        $from = DateTime::createNow()
            ->modify('-' . $period)
            ->toString();

        $count = $this->entityManager
            ->getRDBRepository(TwoFactorCode::ENTITY_TYPE)
            ->where(
                Cond::and(
                    Cond::equal(Cond::column('method'), 'Email'),
                    Cond::equal(Cond::column('userId'), $user->getId()),
                    Cond::greaterOrEqual(Cond::column(Field::CREATED_AT), $from),
                    Cond::lessOrEqual(Cond::column('attemptsLeft'), 0),
                )
            )
            ->count();

        if ($count >= $limit) {
            throw new Forbidden("Max code count exceeded.");
        }
    }

    private function generateCode(): string
    {
        $codeLength = $this->config->get('auth2FAEmailCodeLength') ?? self::CODE_LENGTH;

        $max = pow(10, $codeLength) - 1;

        /** @noinspection PhpUnhandledExceptionInspection */
        return str_pad(
            (string) random_int(0, $max),
            $codeLength,
            '0',
            STR_PAD_LEFT
        );
    }

    private function createEmail(User $user, string $code, string $emailAddress): Email
    {
        $subjectTpl = $this->templateFileManager->getTemplate('twoFactorCode', 'subject');
        $bodyTpl = $this->templateFileManager->getTemplate('twoFactorCode', 'body');

        $htmlizer = $this->htmlizerFactory->create();

        $data = [
            'code' => $code,
        ];

        $subject = $htmlizer->render($user, $subjectTpl, $data, true);
        $body = $htmlizer->render($user, $bodyTpl, $data, true);

        $email = $this->emailFactory->create();

        $email->setSubject($subject);
        $email->setBody($body);
        $email->addToAddress($emailAddress);

        return $email;
    }

    private function inactivateExistingCodeRecords(User $user): void
    {
        $query = $this->entityManager
            ->getQueryBuilder()
            ->update()
            ->in(TwoFactorCode::ENTITY_TYPE)
            ->where([
                'userId' => $user->getId(),
                'method' => EmailLogin::NAME,
            ])
            ->set([
                'isActive' => false,
            ])
            ->build();

        $this->entityManager
            ->getQueryExecutor()
            ->execute($query);
    }

    private function createCodeRecord(User $user, string $code): void
    {
        $this->entityManager->createEntity(TwoFactorCode::ENTITY_TYPE, [
            'code' => $code,
            'userId' => $user->getId(),
            'method' => EmailLogin::NAME,
            'attemptsLeft' => $this->getCodeAttemptsCount(),
        ]);
    }

    private function getUserDataRepository(): UserDataRepository
    {
        /** @var UserDataRepository */
        return $this->entityManager->getRepository(UserData::ENTITY_TYPE);
    }

    private function decrementAttemptsLeft(TwoFactorCode $codeEntity): void
    {
        $codeEntity->decrementAttemptsLeft();

        $this->entityManager->saveEntity($codeEntity);
    }

    private function getCodeAttemptsCount(): int
    {
        return $this->config->get('auth2FAEmailCodeAttemptsCount') ?? self::CODE_ATTEMPTS_COUNT;
    }
}
