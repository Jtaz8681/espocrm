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

export default class DynamicLogicConditionsStringItemBaseView extends View {

    template = 'admin/dynamic-logic/conditions-string/item-base'

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
     * @type {string}
     */
    operatorString

    /**
     * @type {
     *     Record &
     *     {
     *         data: {field?: string},
     *         attribute?: string,
     *     }
     * }
     */
    itemData


    /**
     * @type {Record}
     */
    additionalData

    /**
     * @type {string}
     */
    field

    data() {
        return {
            valueViewKey: this.getValueViewKey(),
            scope: this.scope,
            operator: this.operator,
            operatorString: this.operatorString,
            field: this.field,
            leftString: this.getLeftPartString(),
        };
    }

    setup() {
        this.itemData = this.options.itemData;

        this.level = this.options.level || 0;
        this.number = this.options.number || 0;
        this.scope = this.options.scope;
        this.operator = this.options.operator || this.operator;
        this.operatorString = this.options.operatorString || this.operatorString;
        this.additionalData = (this.itemData.data || {});

        this.field = (this.itemData.data || {}).field || this.itemData.attribute;

        this.wait(true);

        this.isCurrentUser = this.itemData.attribute && this.itemData.attribute.startsWith('$user.');

        if (this.isCurrentUser) {
            this.scope = 'User'
        }

        this.getModelFactory().create(this.scope, model => {
            this.model = model;

            this.populateValues();
            this.createValueFieldView();

            this.wait(false);
        });
    }

    getLeftPartString() {
        if (this.itemData.attribute === '$user.id') {
            return '$' + this.translate('User', 'scopeNames');
        }

        let label = this.translate(this.field, 'fields', this.scope);

        if (this.isCurrentUser) {
            label = '$' + this.translate('User', 'scopeNames') + '.' + label;
        }

        return label;
    }

    populateValues() {
        if (this.itemData.attribute) {
            this.model.set(this.itemData.attribute, this.itemData.value);
        }

        this.model.set(this.additionalData.values || {});
    }

    getValueViewKey() {
        return `view-${this.level.toString()}-${this.number.toString()}-0`;
    }

    getFieldValueView() {
        if (this.itemData.attribute === '$user.id') {
            return 'views/admin/dynamic-logic/fields/user-id';
        }

        const fieldType = this.getMetadata()
            .get(['entityDefs', this.scope, 'fields', this.field, 'type']) || 'base';

        return this.getMetadata().get(['entityDefs', this.scope, 'fields', this.field, 'view']) ||
            this.getFieldManager().getViewName(fieldType);
    }

    createValueFieldView() {
        const key = this.getValueViewKey();

        const viewName = this.getFieldValueView();

        this.createView('value', viewName, {
            model: this.model,
            name: this.field,
            selector: `[data-view-key="${key}"]`,
            readOnly: true,
        });
    }
}
