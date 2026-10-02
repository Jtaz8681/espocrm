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
     * @return {Promise<module:handlers/select-related~filters>}
     */
    async getFilters(model) {
        const acl = this.viewHelper.acl;

        const permission = acl.getPermissionLevel('assignment');

        /** @type {string[]} */
        const boolFilterList = [];

        if (permission === 'team') {
            boolFilterList.push('onlyMyTeam');
        } else if (permission === 'own') {
            boolFilterList.push('onlyMe');
        }

        return {bool: boolFilterList};
    }
}
