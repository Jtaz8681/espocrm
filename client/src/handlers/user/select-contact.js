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

export default class extends SelectRelatedHandler {

    /**
     * @param {import('model').default} model
     */
    async getFilters(model) {
        /** @type {string[]|null} */
        const accountIds = model.attributes.accountsIds;

        if (accountIds && accountIds.length) {
            return {
                advanced: {
                    accounts: {
                        field: 'accounts',
                        type: 'linkedWith',
                        value: accountIds,
                        data: {nameHash: model.attributes.accountsNames || {}},
                    },
                },
            };
        }

        return {};
    }
}
