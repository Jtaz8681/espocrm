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

namespace Espo\Core\Authentication\Hook;

use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\ServiceUnavailable;
use Espo\Core\Utils\Metadata;
use Espo\Core\InjectableFactory;
use Espo\Core\Authentication\AuthenticationData;
use Espo\Core\Api\Request;
use Espo\Core\Authentication\Result;

class Manager
{

    public function __construct(private Metadata $metadata, private InjectableFactory $injectableFactory)
    {}

    /**
     * @throws ServiceUnavailable
     * @throws Forbidden
     */
    public function processBeforeLogin(AuthenticationData $data, Request $request): void
    {
        foreach ($this->getBeforeLoginHookList() as $hook) {
            $hook->process($data, $request);
        }
    }

    /**
     * @throws Forbidden
     */
    public function processOnLogin(Result $result, AuthenticationData $data, Request $request): void
    {
        foreach ($this->getOnLoginHookList() as $hook) {
            $hook->process($result, $data, $request);
        }
    }

    public function processOnFail(Result $result, AuthenticationData $data, Request $request): void
    {
        foreach ($this->getOnFailHookList() as $hook) {
            $hook->process($result, $data, $request);
        }
    }

    public function processOnSuccess(Result $result, AuthenticationData $data, Request $request): void
    {
        foreach ($this->getOnSuccessHookList() as $hook) {
            $hook->process($result, $data, $request);
        }
    }

    public function processOnSuccessByToken(Result $result, AuthenticationData $data, Request $request): void
    {
        foreach ($this->getOnSuccessByTokenHookList() as $hook) {
            $hook->process($result, $data, $request);
        }
    }

    public function processOnSecondStepRequired(Result $result, AuthenticationData $data, Request $request): void
    {
        foreach ($this->getOnSecondStepRequiredHookList() as $hook) {
            $hook->process($result, $data, $request);
        }
    }

    /**
     * @return class-string<BeforeLogin|OnResult>[]
     */
    private function getHookClassNameList(string $type): array
    {
        $key = $type . 'HookClassNameList';

        /** @var class-string<BeforeLogin|OnResult>[] */
        return $this->metadata->get(['app', 'authentication', $key]) ?? [];
    }

    /**
     * @return BeforeLogin[]
     */
    private function getBeforeLoginHookList(): array
    {
        $list = [];

        foreach ($this->getHookClassNameList('beforeLogin') as $className) {
            /** @var class-string<BeforeLogin> $className */
            $list[] = $this->injectableFactory->create($className);
        }

        return $list;
    }

    /**
     * @return OnLogin[]
     */
    private function getOnLoginHookList(): array
    {
        $list = [];

        foreach ($this->getHookClassNameList('onLogin') as $className) {
            /** @var class-string<OnLogin> $className */
            $list[] = $this->injectableFactory->create($className);
        }

        return $list;
    }

    /**
     * @return OnResult[]
     */
    private function getOnFailHookList(): array
    {
        $list = [];

        foreach ($this->getHookClassNameList('onFail') as $className) {
            /** @var class-string<OnResult> $className */
            $list[] = $this->injectableFactory->create($className);
        }

        return $list;
    }

    /**
     * @return OnResult[]
     */
    private function getOnSuccessHookList(): array
    {
        $list = [];

        foreach ($this->getHookClassNameList('onSuccess') as $className) {
            /** @var class-string<OnResult> $className */
            $list[] = $this->injectableFactory->create($className);
        }

        return $list;
    }

    /**
     * @return OnResult[]
     */
    private function getOnSuccessByTokenHookList(): array
    {
        $list = [];

        foreach ($this->getHookClassNameList('onSuccessByToken') as $className) {
            /** @var class-string<OnResult> $className */
            $list[] = $this->injectableFactory->create($className);
        }

        return $list;
    }

    /**
     * @return OnResult[]
     */
    private function getOnSecondStepRequiredHookList(): array
    {
        $list = [];

        foreach ($this->getHookClassNameList('onSecondStepRequired') as $className) {
            /** @var class-string<OnResult> $className */
            $list[] = $this->injectableFactory->create($className);
        }

        return $list;
    }
}
