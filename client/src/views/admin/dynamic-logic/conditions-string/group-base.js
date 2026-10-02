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

import View from 'view';

export default class DynamicLogicConditionsStringGroupBaseView extends View {

    template = 'admin/dynamic-logic/conditions-string/group-base'

    /**
     * @type {number}
     */
    level

    /**
     * @type {string}
     */
    scope

    /**
     * @type {number}
     */
    number

    /**
     * @type {string}
     */
    operator

    /**
     * @type {Record}
     */
    itemData

    /**
     * @type {Record}
     */
    additionalData

    /**
     * @type {string[]}
     */
    viewList

    /**
     * @type {{key: string, isEnd: boolean}[]}
     */
    viewDataList

    data() {
        if (!this.conditionList.length) {
            return {
                isEmpty: true
            };
        }

        return {
            viewDataList: this.viewDataList,
            operator: this.operator,
            level: this.level
        };
    }

    setup() {
        this.level = this.options.level || 0;
        this.number = this.options.number || 0;
        this.scope = this.options.scope;

        this.operator = this.options.operator || this.operator;

        this.itemData = this.options.itemData || {};
        this.viewList = [];

        const conditionList = this.conditionList = this.itemData.value || [];

        this.viewDataList = [];

        conditionList.forEach((item, i) => {
            const key = `view-${this.level.toString()}-${this.number.toString()}-${i.toString()}`;

            this.createItemView(i, key, item);
            this.viewDataList.push({
                key: key,
                isEnd: i === conditionList.length - 1,
            });
        });
    }

    getFieldType(item) {
        return this.getMetadata()
            .get(['entityDefs', this.scope, 'fields', item.attribute, 'type']) || 'base';
    }

    /**
     *
     * @param {number} number
     * @param {string} key
     * @param {{data?: Record, type?: string}} item
     */
    createItemView(number, key, item) {
        this.viewList.push(key);

        item = item || {};

        const additionalData = item.data || {};

        const type = additionalData.type || item.type || 'equals';
        const fieldType = this.getFieldType(item);

        const viewName = this.getMetadata()
            .get(['clientDefs', 'DynamicLogic', 'fieldTypes', fieldType, 'conditionTypes', type, 'itemView']) ||
            this.getMetadata()
                .get(['clientDefs', 'DynamicLogic', 'itemTypes', type, 'view']);

        if (!viewName) {
            return;
        }

        const operator = this.getMetadata()
            .get(['clientDefs', 'DynamicLogic', 'itemTypes', type, 'operator']);

        let operatorString = this.getMetadata()
            .get(['clientDefs', 'DynamicLogic', 'itemTypes', type, 'operatorString']);

        if (!operatorString) {
            operatorString = this.getLanguage()
                .translateOption(type, 'operators', 'DynamicLogic')
                .toLowerCase();

            operatorString = '<i class="small">' + operatorString + '</i>';
        }

        this.createView(key, viewName, {
            itemData: item,
            scope: this.scope,
            level: this.level + 1,
            selector: `[data-view-key="${key}"]`,
            number: number,
            operator: operator,
            operatorString: operatorString,
        });
    }
}
