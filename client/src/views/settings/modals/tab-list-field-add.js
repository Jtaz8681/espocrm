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

import ArrayFieldAddModalView from 'views/modals/array-field-add';

class TabListFieldAddSettingsModalView extends ArrayFieldAddModalView {

    setup() {
        super.setup();

        if (!this.options.noGroups) {
            this.buttonList.push({
                name: 'addGroup',
                text: this.translate('Group Tab', 'labels', 'Settings'),
                onClick: () => this.actionAddGroup(),
                position: 'right',
                iconClass: 'fas fa-plus fa-sm',
            });
        }

        this.buttonList.push({
            name: 'addDivider',
            text: this.translate('Divider', 'labels', 'Settings'),
            onClick: () => this.actionAddDivider(),
            position: 'right',
            iconClass: 'fas fa-plus fa-sm',
        });

        this.addButton({
            name: 'addUrl',
            text: this.translate('URL', 'labels', 'Settings'),
            onClick: () => this.actionAddUrl(),
            position: 'right',
            iconClass: 'fas fa-plus fa-sm',
        });
    }

    actionAddGroup() {
        this.trigger('add', {
            type: 'group',
            text: this.translate('Group Tab', 'labels', 'Settings'),
            iconClass: null,
            color: null,
        });
    }

    actionAddDivider() {
        this.trigger('add', {
            type: 'divider',
            text: null,
        });
    }

    actionAddUrl() {
        this.trigger('add', {
            type: 'url',
            text: this.translate('URL', 'labels', 'Settings'),
            url: null,
            iconClass: null,
            color: null,
            aclScope: null,
            onlyAdmin: false,
        });
    }
}

// noinspection JSUnusedGlobalSymbols
export default TabListFieldAddSettingsModalView;
