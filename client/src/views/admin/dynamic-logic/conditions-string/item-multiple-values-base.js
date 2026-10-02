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

import DynamicLogicConditionsStringItemBaseView from 'views/admin/dynamic-logic/conditions-string/item-base';

export default class extends DynamicLogicConditionsStringItemBaseView {

    template = 'admin/dynamic-logic/conditions-string/item-multiple-values-base'

    data() {
        return {
            valueViewDataList: this.valueViewDataList,
            scope: this.scope,
            operator: this.operator,
            operatorString: this.operatorString,
            field: this.field,
        };
    }

    populateValues() {}

    getValueViewKey(i) {
        return `view-${this.level.toString()}-${this.number.toString()}-${i.toString()}`;
    }

    createValueFieldView() {
        const valueList = this.itemData.value || [];

        const fieldType = this.getMetadata().get(['entityDefs', this.scope, 'fields', this.field, 'type']) || 'base';
        const viewName = this.getMetadata().get(['entityDefs', this.scope, 'fields', this.field, 'view']) ||
            this.getFieldManager().getViewName(fieldType);

        this.valueViewDataList = [];

        valueList.forEach((value, i) => {
            const model = this.model.clone();
            model.set(this.itemData.attribute, value);

            const key = this.getValueViewKey(i);

            this.valueViewDataList.push({
                key: key,
                isEnd: i === valueList.length - 1
            });

            this.createView(key, viewName, {
                model: model,
                name: this.field,
                selector: `[data-view-key="${key}"]`,
                readOnly: true,
            });
        });
    }
}
