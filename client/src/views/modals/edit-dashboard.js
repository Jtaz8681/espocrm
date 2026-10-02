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

import ModalView from 'views/modal';
import Model from 'model';

class EditDashboardModalView extends ModalView {

    template = 'modals/edit-dashboard'

    className = 'dialog dialog-record'
    cssName = 'edit-dashboard'

    data() {
        return {
            hasLocked: this.hasLocked,
        };
    }

    events = {
        /** @this EditDashboardModalView */
        'click button.add': function (e) {
            const name = $(e.currentTarget).data('name');

            this.getParentDashboardView().addDashlet(name);
            this.close();
        },
    }

    shortcutKeys = {
        'Control+Enter': 'save',
    }

    /**
     * @return {module:views/dashboard}
     */
    getParentDashboardView() {
        return /** @type module:views/dashboard */this.getParentView();
    }

    setup() {
        this.buttonList = [
            {
                name: 'save',
                label: this.options.fromDashboard ? 'Save': 'Apply',
                style: 'primary',
                title: 'Ctrl+Enter',
            },
            {
                name: 'cancel',
                label: 'Cancel',
                title: 'Esc',
            }
        ];

        const dashboardLayout = this.options.dashboardLayout || [];

        const dashboardTabList = [];

        dashboardLayout.forEach(item => {
            if (item.name) {
                dashboardTabList.push(item.name);
            }
        });

        const model = this.model = new Model({}, {entityType: 'Preferences'});

        model.set('dashboardTabList', dashboardTabList);

        this.hasLocked = 'dashboardLocked' in this.options;

        if (this.hasLocked) {
            model.set('dashboardLocked', this.options.dashboardLocked || false);
        }

        this.createView('dashboardTabList', 'views/preferences/fields/dashboard-tab-list', {
            selector: '.field[data-name="dashboardTabList"]',
            defs: {
                name: 'dashboardTabList',
                params: {
                    required: true,
                    noEmptyString: true,
                }
            },
            mode: 'edit',
            model: model,
        });

        if (this.hasLocked) {
            this.createView('dashboardLocked', 'views/fields/bool', {
                selector: '.field[data-name="dashboardLocked"]',
                mode: 'edit',
                model: model,
                defs: {
                    name: 'dashboardLocked',
                },
            })
        }

        this.headerText = this.translate('Edit Dashboard');

        this.dashboardLayout = this.options.dashboardLayout;
    }

    /**
     * @param {string} field
     * @return {module:views/fields/base}
     */
    getFieldView(field) {
        return this.getView(field);
    }

    actionSave() {
        const dashboardTabListView = this.getFieldView('dashboardTabList');

        dashboardTabListView.fetchToModel();

        if (this.hasLocked) {
            const dashboardLockedView = this.getFieldView('dashboardLocked');

            dashboardLockedView.fetchToModel();
        }

        if (dashboardTabListView.validate()) {
            return;
        }

        const attributes = {};

        attributes.dashboardTabList = this.model.get('dashboardTabList');

        if (this.hasLocked) {
            attributes.dashboardLocked = this.model.get('dashboardLocked');
        }

        const names = this.model.get('translatedOptions');

        const renameMap = {};

        for (const name in names) {
            if (name !== names[name]) {
                renameMap[name] = names[name];
            }
        }

        attributes.renameMap = renameMap;

        this.trigger('after:save', attributes);

        this.dialog.close();
    }
}

export default EditDashboardModalView;
