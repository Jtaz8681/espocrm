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

import SelectRelatedHandler from 'handlers/select-related';

class SameAccountManySelectRelatedHandler extends SelectRelatedHandler {

    /**
     * @param {module:model} model
     * @return {Promise<module:handlers/select-related~filters>}
     */
    getFilters(model) {
        const advanced = {};

        let accountId = null;
        let accountName = null;

        if (model.get('accountId')) {
            accountId = model.get('accountId');
            accountName = model.get('accountName');
        }

        if (!accountId && model.get('parentType') === 'Account' && model.get('parentId')) {
            accountId = model.get('parentId');
            accountName = model.get('parentName');
        }

        if (accountId) {
            const nameHash = {};
            nameHash[accountId] = accountName;

            advanced.accounts = {
                field: 'accounts',
                type: 'linkedWith',
                value: [accountId],
                data: {nameHash: nameHash},
            };
        }

        return Promise.resolve({
            advanced: advanced,
        });
    }
}

// noinspection JSUnusedGlobalSymbols
export default SameAccountManySelectRelatedHandler;
