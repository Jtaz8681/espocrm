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

namespace Espo\Core\Upgrades\Migrations\V9_1;

use Espo\Core\ORM\Repository\Option\SaveOption;
use Espo\Core\Upgrades\Migration\Script;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\Crypt;
use Espo\Core\Utils\Metadata;
use Espo\Core\Utils\ObjectUtil;
use Espo\Core\Utils\SystemUser;
use Espo\Entities\InboundEmail;
use Espo\Modules\Crm\Entities\KnowledgeBaseArticle;
use Espo\ORM\EntityManager;
use Espo\ORM\Query\Part\Condition;
use Espo\ORM\Query\Part\Expression;
use Espo\Tools\Email\Util;
use stdClass;

class AfterUpgrade implements Script
{
    public function __construct(
        private EntityManager $entityManager,
        private Metadata $metadata,
        private Config $config,
        private Config\ConfigWriter $configWriter,
        private Crypt $crypt,
        private SystemUser $systemUser,
    ) {}

    public function run(): void
    {
        $this->processKbArticles();
        $this->processDynamicLogicMetadata();
        $this->processGroupEmailAccount();
    }

    private function processKbArticles(): void
    {
        if (!str_starts_with(php_sapi_name(), 'cli')) {
            return;
        }

        $articles = $this->entityManager
            ->getRDBRepositoryByClass(KnowledgeBaseArticle::class)
            ->sth()
            ->select([
                'id',
                'body',
                'bodyPlain',
            ])
            ->limit(0, 3000)
            ->find();

        foreach ($articles as $article) {
            $plain = Util::stripHtml($article->getBody() ?? '') ?: null;

            $article->set('bodyPlain', $plain);

            $this->entityManager->saveEntity($article, [SaveOption::SKIP_HOOKS => true]);
        }
    }

    private function processDynamicLogicMetadata(): void
    {
        /** @var string[] $scopes */
        $scopes = array_keys($this->metadata->get('clientDefs', []));

        foreach ($scopes as $scope) {
            $customClientDefs = $this->metadata->getCustom('clientDefs', $scope);

            if (!$customClientDefs instanceof stdClass) {
                continue;
            }

            if (
                !property_exists($customClientDefs, 'dynamicLogic') ||
                !$customClientDefs->dynamicLogic instanceof stdClass
            ) {
                continue;
            }

            $this->metadata->saveCustom('logicDefs', $scope, $customClientDefs->dynamicLogic);

            $customClientDefs = ObjectUtil::clone($customClientDefs);
            unset($customClientDefs->dynamicLogic);

            $this->metadata->saveCustom('clientDefs', $scope, $customClientDefs);
        }
    }

    private function processGroupEmailAccount(): void
    {
        if (!$this->config->get('smtpServer')) {
            return;
        }

        $outboundEmailFromAddress = $this->config->get('outboundEmailFromAddress');

        if (!$outboundEmailFromAddress) {
            return;
        }

        $groupAccount = $this->entityManager
            ->getRDBRepositoryByClass(InboundEmail::class)
            ->where([
                'status' => InboundEmail::STATUS_ACTIVE,
                'useSmtp' => true,
            ])
            ->where(
                Condition::equal(
                    Expression::lowerCase(
                        Expression::column('emailAddress')
                    ),
                    strtolower($outboundEmailFromAddress)
                )
            )
            ->findOne();

        $this->configWriter->set('smtpServer', null);

        if ($groupAccount) {
            $this->configWriter->save();

            return;
        }

        $password = $this->config->get('smtpPassword');

        $groupAccount = $this->entityManager->getRDBRepositoryByClass(InboundEmail::class)->getNew();

        $groupAccount->setMultiple([
            'emailAddress' => $outboundEmailFromAddress,
            'name' => $outboundEmailFromAddress . ' (system)',
            'useImap' => false,
            'useSmtp' => true,
            'smtpHost' => $this->config->get('smtpServer'),
            'smtpPort' => $this->config->get('smtpPort'),
            'smtpAuth' => $this->config->get('smtpAuth'),
            'smtpAuthMechanism' => $this->config->get('smtpAuthMechanism') ?? 'login',
            'fromName' => $this->config->get('outboundEmailFromName'),
            'smtpUsername' => $this->config->get('smtpUsername'),
            'smtpPassword' => $password !== null ? $this->crypt->encrypt($password) : null,
        ]);

        $this->entityManager->saveEntity($groupAccount, [
            SaveOption::SKIP_HOOKS => true,
            SaveOption::CREATED_BY_ID => $this->systemUser->getId(),
        ]);

        $this->configWriter->save();
    }
}
