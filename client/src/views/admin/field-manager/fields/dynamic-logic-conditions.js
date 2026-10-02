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

/**
 * Important. Extended in extensions.
 */
export default class extends BaseFieldView {

    detailTemplate = 'admin/field-manager/fields/dynamic-logic-conditions/detail'
    editTemplate = 'admin/field-manager/fields/dynamic-logic-conditions/edit'

    data() {
        return {
            isSet: this.model.has(this.name),
            isNotEmpty: this.conditionGroup && this.conditionGroup.length,
        };
    }

    setup() {
        this.addActionHandler('editConditions', () => this.edit());

        this.scope = this.params.scope || this.options.scope;
    }

    async prepare() {
        this.conditionGroup = Espo.Utils.cloneDeep((this.model.attributes[this.name] || {}).conditionGroup || []);

        return this.createStringView();
    }

    async createStringView() {
        return this.createView('conditionGroup', 'views/admin/dynamic-logic/conditions-string/group-base', {
            selector: '.top-group-string-container',
            itemData: {
                value: this.conditionGroup
            },
            operator: 'and',
            scope: this.scope,
        });
    }

    edit() {
        this.createView('modal', 'views/admin/dynamic-logic/modals/edit', {
            conditionGroup: this.conditionGroup,
            scope: this.scope,
        }, view => {
            view.render();

            this.listenTo(view, 'apply', async conditionGroup => {
                this.conditionGroup = conditionGroup;

                this.trigger('change');

                await this.createStringView();
                await this.reRender();
            });
        });
    }

    fetch() {
        const data = {};

        data[this.name] = {
            conditionGroup: this.conditionGroup,
        };

        if (this.conditionGroup.length === 0) {
            data[this.name] = null;
        }

        return data;
    }
}
