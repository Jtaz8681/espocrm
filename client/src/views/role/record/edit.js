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

import EditRecordView from 'views/record/edit';

class RoleEditRecordView extends EditRecordView {

    tableView = 'views/role/record/table'

    sideView = false
    isWide = true
    stickButtonsContainerAllTheWay = true

    fetch() {
        const data = super.fetch();

        data['data'] = this.getTableView().fetchScopeData();
        data['fieldData'] = this.getTableView().fetchFieldData();

        return data;
    }

    setup() {
        super.setup();

        this.createView('extra', this.tableView, {
            mode: 'edit',
            selector: '.extra',
            model: this.model,
        }, view => {
            this.listenTo(view, 'change', () => {
                const data = this.fetch();

                this.model.setMultiple(data, {ui: true});
            });
        });
    }

    /**
     * @return {import('./table').default}
     */
    getTableView() {
        return this.getView('extra');
    }
}

export default RoleEditRecordView;
