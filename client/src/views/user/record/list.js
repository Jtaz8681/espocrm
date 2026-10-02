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

import ListRecordView from 'views/record/list';

export default class UserListRecordView extends ListRecordView {

    rowActionsView = 'views/user/record/row-actions/default'

    quickEditDisabled = true
    massActionList = ['remove', 'massUpdate', 'export']
    checkAllResultMassActionList = ['massUpdate', 'export']

    setupMassActionItems() {
        super.setupMassActionItems();

        if (this.scope === 'ApiUser') {
            this.removeMassAction('massUpdate');
            this.removeMassAction('export');

            this.layoutName = 'listApi';
        }

        if (this.scope === 'PortalUser') {
            this.layoutName = 'listPortal';
        }

        if (!this.getUser().isAdmin()) {
            this.removeMassAction('massUpdate');
            this.removeMassAction('export');
        }
    }

    getModelScope(id) {
        const model = /** @type {import('models/user').default} */
            this.collection.get(id);

        if (model.isPortal()) {
            return 'PortalUser';
        }

        return this.scope;
    }
}
