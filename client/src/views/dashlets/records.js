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

import RecordListDashletView from 'views/dashlets/abstract/record-list';

class RecordsDashletView extends RecordListDashletView {

    name = 'Records'

    rowActionsView = 'views/record/row-actions/view-and-edit'
    listView = 'views/email/record/list-expanded'

    init() {
        super.init();

        this.scope = this.getOption('entityType');
    }

    getSearchData() {
        const data = {
            primary: /** @type string */this.getOption('primaryFilter'),
        };

        if (data.primary === 'all') {
            delete data.primary;
        }

        const bool = {};

        (this.getOption('boolFilterList') || []).forEach(item => {
            bool[item] = true;
        });

        data.bool = bool;

        return data;
    }

    setupActionList() {
        const scope = this.getOption('entityType');

        if (scope && this.getAcl().checkScope(scope, 'create')) {
            this.actionList.unshift({
                name: 'create',
                text: this.translate('Create ' + scope, 'labels', scope),
                iconClass: 'fas fa-plus',
                url: `#${scope}/create`,
            });
        }
    }
}

export default RecordsDashletView;
