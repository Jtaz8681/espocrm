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

export default class extends ModalView {

    template = 'admin/dynamic-logic/modals/edit'

    className = 'dialog dialog-record'

    data() {
        return {};
    }

    setup() {
        this.conditionGroup = Espo.Utils.cloneDeep(this.options.conditionGroup || []);
        this.scope = this.options.scope;

        this.buttonList = [
            {
                name: 'apply',
                label: 'Apply',
                style: 'primary',
                onClick: () => this.actionApply(),
            },
            {
                name: 'cancel',
                label: 'Cancel',
            }
        ];

        this.createView('conditionGroup', 'views/admin/dynamic-logic/conditions/and', {
            selector: '.top-group-container',
            itemData: {
                value: this.conditionGroup,
            },
            scope: this.options.scope,
        });
    }

    actionApply() {
        const conditionGroupView = /** @type {import('../conditions/and').default} */
            this.getView('conditionGroup');

        const data = conditionGroupView.fetch();

        const conditionGroup = data.value;

        this.trigger('apply', conditionGroup);
        this.close();
    }
}
