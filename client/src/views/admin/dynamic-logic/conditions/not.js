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

import DynamicLogicConditionGroupBaseView from 'views/admin/dynamic-logic/conditions/group-base';

export default class extends DynamicLogicConditionGroupBaseView {

    template = 'admin/dynamic-logic/conditions/not'

    operator = 'not'

    data() {
        return {
            viewKey: this.viewKey,
            operator: this.operator,
            hasItem: this.hasView(this.viewKey),
            level: this.level,
            groupOperator: this.getGroupOperator(),
        };
    }

    setup() {
        this.level = this.options.level || 0;
        this.number = this.options.number || 0;
        this.scope = this.options.scope;

        this.itemData = this.options.itemData || {};
        this.viewList = [];

        const i = 0;
        const key = this.getKey();

        if (this.itemData.value) {
            this.createItemView(i, key, this.itemData.value);
        }

        this.viewKey = key;
    }

    removeItem() {
        const key = this.getKey();

        this.clearView(key);

        this.controlAddItemVisibility();
    }

    getKey() {
        const i = 0;

        return `view-${this.level.toString()}-${this.number.toString()}-${i.toString()}`;
    }

    getIndexForNewItem() {
        return 0;
    }

    addItemContainer() {}

    addViewDataListItem() {}

    fetch() {
        /** @type {import('./field-types/base').default} */
        const view = this.getView(this.viewKey);

        if (!view) {
            return {
                type: 'and',
                value: [],
            };
        }

        const value = view.fetch();

        return {
            type: this.operator,
            value: value,
        };
    }

    controlAddItemVisibility() {
        if (this.getView(this.getKey())) {
            this.$el.find(' > .group-bottom').addClass('hidden');
        } else {
            this.$el.find(' > .group-bottom').removeClass('hidden');
        }
    }
}
