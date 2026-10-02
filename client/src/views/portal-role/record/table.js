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

import RoleRecordTableView from 'views/role/record/table';

export default class extends RoleRecordTableView {

    levelListMap = {
        'recordAllAccountContactOwnNo': ['all', 'account', 'contact', 'own', 'no'],
        'recordAllAccountOwnNo': ['all', 'account', 'own', 'no'],
        'recordAllContactOwnNo': ['all', 'contact', 'own', 'no'],
        'recordAllAccountNo': ['all', 'account', 'no'],
        'recordAllContactNo': ['all', 'contact', 'no'],
        'recordAllAccountContactNo': ['all', 'account', 'contact', 'no'],
        'recordAllOwnNo': ['all', 'own', 'no'],
        'recordAllNo': ['all', 'no'],
        'record': ['all', 'own', 'no']
    }

    levelList = [
        'all',
        'account',
        'contact',
        'own',
        'no',
    ]

    type = 'aclPortal'
    lowestLevelByDefault = true

    setupScopeList() {
        this.aclTypeMap = {};
        this.scopeList = [];

        const scopeListAll = this.getSortedScopeList();

        scopeListAll.forEach(scope => {
            if (
                this.getMetadata().get(`scopes.${scope}.disabled`) ||
                this.getMetadata().get(`scopes.${scope}.disabledPortal`)
            ) {
                return;
            }

            const acl = this.getMetadata().get(`scopes.${scope}.aclPortal`);

            if (acl) {
                this.scopeList.push(scope);
                this.aclTypeMap[scope] = acl;

                if (acl === true) {
                    this.aclTypeMap[scope] = 'record';
                }
            }
        });
    }

    isAclFieldLevelDisabledForScope(scope) {
        return !!this.getMetadata().get(`scopes.${scope}.aclPortalFieldLevelDisabled`);
    }
}
