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

namespace Espo\Core\Authentication\Oidc\UserProvider;

use Espo\Entities\User;

class DefaultUserInfoPopulator implements UserInfoPopulator
{
    public function populate(UserInfo $userInfo, User $user): void
    {
        $values = $this->getUserDataFromToken($userInfo);

        $user->setMultiple($values);
    }

    /**
     * @return array<string, mixed>
     */
    private function getUserDataFromToken(UserInfo $userInfo): array
    {
        return [
            'emailAddress' => $userInfo->get('email'),
            'phoneNumber' => $userInfo->get('phone_number'),
            'firstName' => $userInfo->get('given_name'),
            'lastName' => $userInfo->get('family_name'),
            'middleName' => $userInfo->get('middle_name'),
            'gender' =>
                in_array($userInfo->get('gender'), ['male', 'female']) ?
                    ucfirst($userInfo->get('gender') ?? '') :
                    null,
        ];
    }
}
