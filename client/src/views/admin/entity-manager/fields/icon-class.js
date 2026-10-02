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

import BaseFieldView from 'views/fields/base';

export default class extends BaseFieldView {

    editTemplate = 'admin/entity-manager/fields/icon-class/edit'

    setup() {
        super.setup();

        this.addActionHandler('selectIcon', () => this.selectIcon());
    }

    selectIcon() {
        this.createView('dialog', 'views/admin/entity-manager/modals/select-icon', {}, view => {
            view.render();

            this.listenToOnce(view, 'select', value => {
                if (value === '') {
                    value = null;
                }

                this.model.set(this.name, value);

                view.close();
            });
        });
    }

    fetch() {
        const data = {};

        data[this.name] = this.model.get(this.name);

        return data;
    }
}
