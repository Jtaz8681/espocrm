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

import DynamicLogicConditionsStringGroupBaseView from 'views/admin/dynamic-logic/conditions-string/group-base';

export default class DynamicLogicConditionsStringGroupNotView extends DynamicLogicConditionsStringGroupBaseView {

    template = 'admin/dynamic-logic/conditions-string/group-not'

    data() {
        return {
            viewKey: this.viewKey,
            operator: this.operator,
        };
    }

    setup() {
        this.level = this.options.level || 0;
        this.number = this.options.number || 0;
        this.scope = this.options.scope;
        this.operator = this.options.operator || this.operator;
        this.itemData = this.options.itemData || {};
        this.viewList = [];

        const i = 0;
        const key = `view-${this.level.toString()}-${this.number.toString()}-${i.toString()}`;

        this.createItemView(i, key, this.itemData.value);
        this.viewKey = key;
    }
}
