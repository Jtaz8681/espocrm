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

import DynamicLogicConditionFieldTypeBaseView from 'views/admin/dynamic-logic/conditions/field-types/base';

export default class extends DynamicLogicConditionFieldTypeBaseView {

    fetch() {
        /** @type {import('views/fields/base').default} */
        const valueView = this.getView('value');

        const item = {
            type: this.type,
            attribute: this.field,
        };

        if (valueView) {
            valueView.fetchToModel();
            item.value = this.model.get(this.field);
        }

        return item;
    }

    getValueViewName() {
        let viewName = super.getValueViewName();

        if (['has', 'notHas'].includes(this.type)) {
            viewName = 'views/fields/enum';
        }

        return viewName;
    }
}
